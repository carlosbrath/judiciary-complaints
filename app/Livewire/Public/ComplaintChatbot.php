<?php

namespace App\Livewire\Public;

use App\Enums\ComplaintCategory;
use App\Livewire\Citizen\FileComplaint;
use App\Livewire\Concerns\HandlesComplaintUploads;
use App\Livewire\Forms\CitizenForm;
use App\Livewire\Forms\ComplaintForm;
use App\Models\ChatbotFaq;
use App\Models\ChatbotQuestion as Question;
use App\Models\ChatConversation;
use App\Models\Citizen;
use App\Models\Complaint;
use App\Models\Court;
use App\Services\ChatLogger;
use App\Services\CitizenAccountService;
use App\Services\ComplaintSubmissionService;
use App\Support\Countries;
use App\Support\Site;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Scripted, rule-based assistant (no AI): a main menu, FAQs, contact details, status lookup by
 * tracking number + CNIC/passport, and filing a complaint one question at a time. Answers are
 * validated with the same rules as the citizen form and the complaint is registered through
 * ComplaintSubmissionService, so it is identical to one filed with the form.
 */
#[Layout('components.layouts.public')]
class ComplaintChatbot extends Component
{
    use HandlesComplaintUploads;

    public const SESSION_KEY = 'chatbot.conversation';

    /** Messages (typed or tapped) per minute per connection. */
    public const MAX_ACTIONS_PER_MINUTE = 30;

    public const MENU = 'menu';
    public const FILE = 'file';
    public const REVIEW = 'review';
    public const DONE = 'done';
    public const TRACK = 'track';
    public const FAQ = 'faq';

    /** A chat resumed after this long greets the citizen again. */
    private const WELCOME_BACK_MINUTES = 30;

    /** A filed complaint's chat stays on screen this long, then a fresh chat starts. */
    private const SHOW_COMPLETED_MINUTES = 30;

    private const SKIP_WORDS = ['skip', 'no', 'none', 'nothing', 'nahi', 'nahin', 'nai', 'نہیں', 'ںیہن', 'کوئی نہیں', 'چھوڑیں'];

    private const MENU_WORDS = ['menu', 'main menu', 'start', 'home', 'back', 'help', 'hi', 'hello', 'salam', 'مینو', 'واپس', 'مدد'];

    /** Typed words that pick a main-menu item (English, Urdu and Roman Urdu). */
    private const INTENTS = [
        'track' => ['track', 'status', 'tracking', 'check', 'progress', 'ojc', 'ٹریک', 'صورتحال', 'اسٹیٹس', 'کیفیت'],
        'file' => ['file', 'new complaint', 'register', 'lodge', 'submit', 'complain', 'darj', 'shikayat', 'درج', 'شکایت', 'نئی'],
        'contact' => ['contact', 'phone', 'call', 'email', 'address', 'helpline', 'whatsapp', 'office', 'رابطہ', 'فون', 'پتہ', 'ہیلپ لائن'],
        'faq' => ['faq', 'how it works', 'question', 'how', 'سوال', 'طریقہ', 'کیسے'],
    ];

    public ComplaintForm $form;

    /** Rendered inside the floating chat window rather than as its own page. */
    #[Locked]
    public bool $embedded = false;

    /** @var list<array{id: string, from: string, text: string, tone: ?string, help: ?string, key: ?string}> */
    #[Locked]
    public array $messages = [];

    /** menu | file | review | done | track | faq */
    #[Locked]
    public string $mode = self::MENU;

    /** Key of the filing question waiting for an answer. */
    #[Locked]
    public ?string $current = null;

    /** @var list<string> Keys of filing questions already answered or skipped. */
    #[Locked]
    public array $asked = [];

    /** Name, country, WhatsApp and CNIC/passport for the citizen's account. */
    #[Locked]
    public array $identity = ['name' => '', 'country' => '', 'phone' => '', 'cnic_passport' => ''];

    /** @var array<string, array{question: string, answer: string, display: string}> Answers to custom questions. */
    #[Locked]
    public array $answers = [];

    /** Changing an answer from the summary; the bot returns to the summary afterwards. */
    #[Locked]
    public bool $editing = false;

    /** Tracking number of the complaint filed in this chat. */
    #[Locked]
    public ?string $trackingNumber = null;

    /** Status lookup: number | identity | result */
    #[Locked]
    public ?string $trackStep = null;

    #[Locked]
    public string $trackRef = '';

    /** Narrows a long option list after an ambiguous typed answer ("Mirpur" → its three courts). */
    #[Locked]
    public string $hint = '';

    /** Messages carried over from an earlier page; shown at once instead of being "typed" again. */
    #[Locked]
    public int $restoredCount = 0;

    #[Locked]
    public ?int $conversationId = null;

    /** How many of $messages are already in chat_messages. */
    #[Locked]
    public int $loggedCount = 0;

    public string $reply = '';

    public function mount(bool $embedded = false): void
    {
        $this->embedded = $embedded;

        $conversation = ChatConversation::query()->find(session(self::SESSION_KEY));

        if ($conversation && $this->restore($conversation)) {
            return;
        }

        session()->forget(self::SESSION_KEY);
        $this->start();
    }

    /** Saved after every request, so the chat follows the citizen across pages. Nothing is logged until they send something. */
    public function dehydrate(): void
    {
        $logger = app(ChatLogger::class);
        $spoke = collect($this->messages)->contains('from', 'user');

        if (! $this->conversationId && $spoke) {
            $this->conversationId = $logger->start($this->citizen()?->id)->id;
            session()->put(self::SESSION_KEY, $this->conversationId);
        }

        if ($conversation = $this->conversationId ? ChatConversation::query()->find($this->conversationId) : null) {
            $this->loggedCount = $logger->sync($conversation, $this->status(), $this->state(), $this->messages, $this->loggedCount);
        }
    }

    public function placeholder()
    {
        return view('livewire.public.complaint-chatbot-placeholder');
    }

    #[Title('Judiciary Assistant')]
    public function render()
    {
        return view('livewire.public.complaint-chatbot', [
            'question' => $this->currentQuestion(),
            'options' => $this->visibleOptions(),
            'limits' => $this->limits(),
        ]);
    }

    // ---------------------------------------------------------------- data

    #[Computed]
    public function questions(): Collection
    {
        return Question::forChat();
    }

    #[Computed(persist: true, seconds: 600)]
    public function courts(): Collection
    {
        return Court::query()->active()->ordered()->get();
    }

    #[Computed]
    public function faqs(): Collection
    {
        return ChatbotFaq::query()->active()->ordered()->get();
    }

    public function citizen(): ?Citizen
    {
        return Auth::guard('citizen')->user();
    }

    public function currentQuestion(): ?Question
    {
        return $this->mode === self::FILE && $this->current ? $this->questions->get($this->current) : null;
    }

    /** Percentage of filing questions answered, for the progress bar. */
    public function progress(): int
    {
        if (in_array($this->mode, [self::REVIEW, self::DONE], true)) {
            return 100;
        }

        $total = $this->questions->count();

        return $total ? (int) round(count(array_intersect($this->asked, $this->questions->keys()->all())) / $total * 100) : 0;
    }

    public function filingInProgress(): bool
    {
        return ! $this->trackingNumber && ($this->asked !== [] || $this->mode === self::REVIEW);
    }

    /** @return array<string, string> Main menu: value => label */
    public function menuOptions(): array
    {
        return [
            'file' => $this->filingInProgress() ? __('Continue my complaint') : __('File a Complaint'),
            'track' => __('Track My Complaint'),
            'faq' => __('How It Works / FAQs'),
            'contact' => __('Contact Information'),
        ];
    }

    /**
     * Quick replies for the current step.
     *
     * @return array<int|string, string> value => label in the current locale
     */
    public function options(): array
    {
        return match ($this->mode) {
            self::MENU => $this->menuOptions(),
            self::FAQ => $this->faqs->mapWithKeys(fn (ChatbotFaq $faq) => [(string) $faq->id => $faq->localized('question')])->all(),
            self::TRACK => match ($this->trackStep) {
                'number' => $this->citizen()?->complaints()->latest()->limit(6)->pluck('tracking_number', 'tracking_number')->all() ?? [],
                'result' => ['again' => __('Track another complaint')],
                default => [],
            },
            self::FILE => ($question = $this->currentQuestion()) ? $this->optionsFor($question) : [],
            default => [],
        };
    }

    /** Options after narrowing by $hint. */
    private function visibleOptions(): array
    {
        $options = $this->options();

        if ($this->hint === '') {
            return $options;
        }

        $needle = Str::lower($this->hint);
        $narrowed = array_filter($options, fn ($label) => Str::contains(Str::lower($label), $needle));

        return $narrowed ?: $options;
    }

    /** @return array<int|string, string> */
    public function optionsFor(Question $question): array
    {
        return match ($question->type) {
            Question::TYPE_COUNTRY => Countries::options(),
            Question::TYPE_COURT => $this->courts->mapWithKeys(fn (Court $c) => [$c->id => $c->label])->all(),
            Question::TYPE_CATEGORY => ComplaintCategory::options(),
            Question::TYPE_YES_NO => ['yes' => __('Yes'), 'no' => __('No')],
            Question::TYPE_CHOICE => $question->choiceLabels(),
            default => [],
        };
    }

    /**
     * Summary shown before submitting.
     *
     * @return list<array{key: string, label: string, value: string}>
     */
    public function summary(): array
    {
        $rows = [];

        foreach ($this->questions as $key => $question) {
            if ($this->citizen() && in_array($key, Question::IDENTITY_KEYS, true)) {
                continue; // taken from the account
            }

            $value = match ($key) {
                'name', 'phone', 'cnic_passport' => $this->identity[$key],
                'country' => Countries::name($this->identity['country']),
                'court_id' => $this->courts->firstWhere('id', (int) $this->form->court_id)?->label,
                'category' => ComplaintCategory::tryFrom($this->form->category)?->label(),
                'case_number', 'case_title' => $this->form->{$key},
                'description' => Str::limit($this->form->description, 300),
                'attachments' => $this->attachments ? trans_choice(':count file attached|:count files attached', count($this->attachments)) : null,
                default => $this->answers[$key]['display'] ?? null,
            };

            $rows[] = [
                'key' => $key,
                'label' => $question->is_system ? $this->fieldLabel($key) : Str::limit($question->localized('question'), 60),
                'value' => filled($value) ? (string) $value : '—',
            ];
        }

        return $rows;
    }

    // ---------------------------------------------------------------- actions

    /** Something typed into the message box. */
    public function send(): void
    {
        $text = trim(Str::limit($this->reply, 5000, ''));
        $this->reply = '';

        if ($text === '' || $this->throttled()) {
            return;
        }

        $this->user($text);
        $this->hint = '';
        $lower = Str::lower($text);

        if (in_array($lower, self::MENU_WORDS, true)) {
            $this->showMenu();

            return;
        }

        match ($this->mode) {
            self::FILE => $this->answer($text),
            self::TRACK => $this->trackInput($text),
            self::REVIEW => $this->bot(__('Please check the summary above and submit — or tap the pencil to change an answer.')),
            default => $this->understand($text),
        };
    }

    /** A tapped quick reply. */
    public function choose(string $value): void
    {
        $options = $this->options();

        if (! array_key_exists($value, $options) || $this->throttled()) {
            return;
        }

        $this->user($options[$value]);
        $this->hint = '';

        match ($this->mode) {
            self::MENU => $this->runIntent($value),
            self::FAQ => $this->answerFaq($this->faqs->firstWhere('id', (int) $value)),
            self::TRACK => $value === 'again' ? $this->startTracking() : $this->lookUp($value, owned: true),
            self::FILE => $this->accept($this->currentQuestion(), $value),
            default => null,
        };
    }

    /** "☰ Menu" from any step; filing answers are kept. */
    public function menu(): void
    {
        if ($this->mode === self::MENU || $this->throttled()) {
            return;
        }

        $this->user(__('Main menu'));
        $this->showMenu();
    }

    public function skip(): void
    {
        $question = $this->currentQuestion();

        if (! $question || $question->is_required || $this->throttled()) {
            return;
        }

        $this->user($question->type === Question::TYPE_ATTACHMENTS ? __('No attachments') : __('Skip'));
        $this->skipQuestion($question);
    }

    public function clearHint(): void
    {
        $this->hint = '';
    }

    public function attachmentsDone(): void
    {
        $question = $this->currentQuestion();

        if ($question?->type !== Question::TYPE_ATTACHMENTS || ! $this->attachments) {
            return;
        }

        $this->user('📎 '.trans_choice(':count file attached|:count files attached', count($this->attachments)));
        $this->done($question);
    }

    /** Change one answer from the summary. */
    public function edit(string $key): void
    {
        if ($this->mode !== self::REVIEW || ! $this->questions->has($key)) {
            return;
        }

        $this->mode = self::FILE;
        $this->editing = true;
        $this->forget($key);
        $this->resetErrorBag();
        $this->askNext(__('Sure, let’s change that.'));
    }

    public function submit(ComplaintSubmissionService $submissions, CitizenAccountService $accounts): void
    {
        if ($this->mode !== self::REVIEW) {
            return;
        }

        $key = FileComplaint::rateLimitKey();

        if (RateLimiter::tooManyAttempts($key, FileComplaint::MAX_SUBMISSIONS)) {
            $this->addError('submit', __('Too many complaints from this connection. Please try again in :minutes minutes.', [
                'minutes' => ceil(RateLimiter::availableIn($key) / 60),
            ]));

            return;
        }

        $this->validate($this->fileRules(), attributes: $this->fileAttributes());

        $citizen = $this->citizen();

        if (! $citizen) {
            $validator = Validator::make($this->identity, CitizenForm::identityRules(), CitizenForm::messageTexts(), CitizenForm::attributeNames());

            if ($validator->fails()) {
                $this->reopen((string) array_key_first($validator->errors()->messages()), $validator->errors()->first());

                return;
            }
        }

        try {
            $data = $this->form->payload();
        } catch (ValidationException $e) {
            $field = Str::after((string) array_key_first($e->errors()), 'form.');

            // Declaration and honeypot errors are shown on the summary card; anything else is asked again.
            if (! $this->questions->has($field)) {
                throw $e;
            }

            $this->resetErrorBag();
            $this->reopen($field, collect($e->errors())->flatten()->first());

            return;
        }

        if ($this->answers) {
            $extra = collect($this->answers)->map(fn (array $a) => '• '.$a['question'].': '.$a['answer'])->join("\n");
            $data['description'] .= "\n\nAdditional information:\n".$extra;
        }

        RateLimiter::hit($key, FileComplaint::DECAY_SECONDS);

        $citizen ??= $accounts->forChat(Arr::only($this->identity, Question::IDENTITY_KEYS));
        $complaint = $submissions->submit($citizen, $data, $this->attachments, Complaint::VIA_CHATBOT, request()->ip());

        $this->trackingNumber = $complaint->tracking_number;
        $this->mode = self::DONE;
        $this->current = null;

        $this->bot(__('Your complaint has been registered. Your tracking number is :number — please keep it safe.', ['number' => $complaint->tracking_number]), 'success');
        $this->bot($this->citizen()
            ? __('You can follow it, with every review from the court administration, under “My complaints”.')
            : __('To follow it online, create an account with the same CNIC/passport and WhatsApp number — the complaint will appear on your dashboard. You can also track it here at any time.'));
    }

    /** "Start over" / "File another complaint": close this conversation and begin a new one. */
    public function restart(ChatLogger $logger): void
    {
        if ($old = ChatConversation::query()->find($this->conversationId)) {
            $logger->restart($old);
        }

        session()->forget(self::SESSION_KEY);

        // The new conversation is only recorded once the citizen sends something.
        $this->reset('messages', 'mode', 'current', 'asked', 'identity', 'answers', 'editing', 'trackingNumber', 'trackStep', 'trackRef',
            'hint', 'restoredCount', 'conversationId', 'loggedCount', 'reply', 'attachments', 'pendingAttachments');
        $this->form->reset();
        $this->resetErrorBag();
        $this->start();
    }

    // ---------------------------------------------------------------- menu, FAQs, contact

    private function start(): void
    {
        $this->bot(__('Assalam-o-Alaikum! I am the :bot of the Lower Judiciary of AJ&K. I can help overseas citizens file a complaint about a court case and check its status.', ['bot' => __('Judiciary Assistant')]));

        if ($citizen = $this->citizen()) {
            $this->bot(__('You are logged in as :name, so complaints you file here go straight to your account.', ['name' => $citizen->name]));
        }

        $this->showMenu();
    }

    private function showMenu(?string $lead = null): void
    {
        $this->mode = self::MENU;
        $this->trackStep = null;
        $this->bot($lead ?? ($this->filingInProgress()
            ? __('How can I help? Your complaint is saved — choose “Continue my complaint” to finish it.')
            : __('How can I help you today? Choose an option or type your question.')));
    }

    /** Free text outside a flow: a menu item, else an FAQ, else a polite fallback. */
    private function understand(string $text): void
    {
        if ($this->mode === self::FAQ && ($faq = $this->faqs->first(fn (ChatbotFaq $f) => Str::lower($f->localized('question')) === Str::lower($text)))) {
            $this->answerFaq($faq);

            return;
        }

        if (preg_match('/\bOJC[\s-]*\d{4}[\s-]*\d{1,6}\b/i', $text, $m)) {
            $this->startTracking(silent: true);
            $this->trackInput($m[0]);

            return;
        }

        // A menu item typed out ("track my complaint") runs it.
        if ($intent = array_search(Str::lower($text), array_map(fn ($l) => Str::lower($l), $this->menuOptions()), true)) {
            $this->runIntent($intent);

            return;
        }

        // Questions ("how do I file…?") get an answer; commands ("file complaint") run the menu item.
        $faq = ChatbotFaq::bestMatch($this->faqs, $text);

        if ($faq && self::looksLikeQuestion($text)) {
            $this->answerFaq($faq);

            return;
        }

        if ($intent = $this->intentOf($text)) {
            $this->runIntent($intent);

            return;
        }

        if ($faq) {
            $this->answerFaq($faq);

            return;
        }

        $this->bot(__('Sorry, I didn’t quite understand that. I can help with the options below — or call our helpline on :phone.', ['phone' => Site::get('helpline')]), 'error');
        $this->mode === self::FAQ ? $this->bot(__('Choose one of the questions below, or type “menu”.')) : $this->showMenu(__('What would you like to do?'));
    }

    private static function looksLikeQuestion(string $text): bool
    {
        return str_contains($text, '?') || str_contains($text, '؟')
            || (bool) preg_match('/^(how|what|who|when|where|why|which|can|do|does|is|are|will|kya|kaise|kab|کیا|کیسے|کب|کون|کہاں|کتنا)/iu', trim($text));
    }

    private function intentOf(string $text): ?string
    {
        $lower = Str::lower($text);

        foreach (self::INTENTS as $intent => $words) {
            foreach ($words as $word) {
                if (Str::contains($lower, $word)) {
                    return $intent;
                }
            }
        }

        return null;
    }

    private function runIntent(string $intent): void
    {
        // After a complaint has been filed, a new one starts in a fresh chat.
        if ($intent === 'file' && $this->trackingNumber) {
            $this->restart(app(ChatLogger::class));
        }

        match ($intent) {
            'file' => $this->startFiling(),
            'track' => $this->startTracking(),
            'faq' => $this->showFaqs(),
            'contact' => $this->showContact(),
            default => $this->showMenu(),
        };
    }

    private function showFaqs(): void
    {
        $this->mode = self::FAQ;
        $this->bot(__('Here are the questions people ask most. Tap one, or type your own question.'));
    }

    private function answerFaq(?ChatbotFaq $faq): void
    {
        if (! $faq) {
            return;
        }

        $this->mode = self::FAQ;

        foreach ($this->paragraphs($faq->localized('answer')) as $paragraph) {
            $this->bot($paragraph);
        }

        $this->bot(__('Anything else? Pick another question, or type “menu” to go back.'));
    }

    private function showContact(): void
    {
        $contact = Site::all();

        $this->bot(implode("\n", array_filter([
            __('You can reach the Overseas Complaint Cell here:'),
            '📞 '.__('Helpline:').' '.$contact['helpline'],
            '💬 '.__('WhatsApp:').' '.$contact['whatsapp'],
            '✉️ '.__('Email:').' '.$contact['email'],
            '📍 '.__($contact['address']),
            $contact['hours'] ? '🕘 '.__($contact['hours']) : null,
        ])));

        $this->showMenu(__('Anything else I can help you with?'));
    }

    // ---------------------------------------------------------------- tracking

    private function startTracking(bool $silent = false): void
    {
        $this->mode = self::TRACK;
        $this->trackStep = 'number';
        $this->trackRef = '';

        if ($silent) {
            return;
        }

        $this->bot($this->citizen()?->complaints()->exists()
            ? __('Pick one of your complaints below, or type a tracking number (e.g. OJC-2026-00001).')
            : __('Please type your tracking number. It looks like OJC-2026-00001.'));
    }

    private function trackInput(string $text): void
    {
        if ($this->trackStep === 'identity') {
            $this->lookUp($this->trackRef, identity: $text);

            return;
        }

        if ($this->trackStep === 'result' && ! preg_match('/OJC/i', $text)) {
            $this->understand($text);

            return;
        }

        $ref = self::normalizeTrackingNumber($text);

        if (! $ref) {
            $this->bot(__('That doesn’t look like a tracking number. It should look like OJC-2026-00001.'), 'error');
            $this->trackStep = 'number';

            return;
        }

        // A logged-in citizen's own complaint needs no CNIC/passport.
        if ($this->citizen()?->complaints()->where('tracking_number', $ref)->exists()) {
            $this->lookUp($ref, owned: true);

            return;
        }

        $this->trackRef = $ref;
        $this->trackStep = 'identity';
        $this->bot(__('Thanks. Now please enter the CNIC/NICOP or passport number the complaint was filed with.'));
    }

    private function lookUp(string $ref, bool $owned = false, ?string $identity = null): void
    {
        $key = TrackComplaint::rateLimitKey();

        if (RateLimiter::tooManyAttempts($key, TrackComplaint::MAX_ATTEMPTS)) {
            $this->bot(__('Too many attempts. Please try again in :minutes minutes.', ['minutes' => ceil(RateLimiter::availableIn($key) / 60)]), 'error');
            $this->trackStep = 'result';

            return;
        }

        RateLimiter::hit($key, 600);

        $complaint = $owned
            ? $this->citizen()?->complaints()->where('tracking_number', $ref)->with(['court', 'latestReview'])->first()
            : Complaint::findForTracking($ref, (string) $identity);

        $this->trackStep = 'result';

        if (! $complaint) {
            $this->bot(__('I couldn’t find a complaint with that tracking number and CNIC/passport. Please check both and try again.'), 'error');

            return;
        }

        $this->bot(implode("\n", [
            __('Complaint :number', ['number' => $complaint->tracking_number]),
            __('Status:').' '.$complaint->status->label(),
            __('Court:').' '.$complaint->court->label,
            __('Case:').' '.$complaint->case_number.' — '.$complaint->case_title,
            __('Filed on:').' '.$complaint->created_at->format('d M Y'),
        ]), 'success');

        $review = $complaint->latestReview;
        $this->bot($review
            ? __('Latest review (:date):', ['date' => $review->created_at->format('d M Y')])."\n".$review->body
            : __('There are no reviews yet. The court administration will post remarks here as they look into it.'));
    }

    /** "ojc 2026 42" → "OJC-2026-00042"; null if it isn't one. */
    public static function normalizeTrackingNumber(string $text): ?string
    {
        if (! preg_match('/^\s*OJC[\s-]*(\d{4})[\s-]*(\d{1,6})\s*$/i', $text, $m)) {
            return null;
        }

        return sprintf('OJC-%s-%05d', $m[1], (int) $m[2]);
    }

    // ---------------------------------------------------------------- filing

    private function startFiling(): void
    {
        $resuming = $this->filingInProgress();
        $this->mode = self::FILE;

        if ($citizen = $this->citizen()) {
            $this->identity = $citizen->only(Question::IDENTITY_KEYS);
            $this->asked = array_values(array_unique([...$this->asked, ...Question::IDENTITY_KEYS]));
        }

        if ($resuming && ! $this->current && $this->asked && count(array_diff($this->questions->keys()->all(), $this->asked)) === 0) {
            $this->askNext(); // everything answered: straight back to the summary

            return;
        }

        $this->askNext($resuming
            ? __('Welcome back — let’s carry on.')
            : __('I’ll ask you a few questions, one at a time. You can type “menu” at any time; your answers are kept.'));
    }

    /** A typed answer to the current filing question. */
    private function answer(string $text): void
    {
        $question = $this->currentQuestion();

        if (! $question) {
            $this->askNext();

            return;
        }

        $options = $this->optionsFor($question);

        if (! $question->is_required && in_array(Str::lower($text), self::SKIP_WORDS, true)
            && (! $options || $question->type !== Question::TYPE_YES_NO)) {
            $this->skipQuestion($question);

            return;
        }

        if ($options) {
            $matches = $this->match($question, $text, $options);

            if (count($matches) === 1) {
                $this->accept($question, (string) $matches[0]);
            } elseif ($matches) {
                $this->hint = Str::limit($text, 40, '');
                $this->bot(__('Several options match “:text”. Please pick the right one below.', ['text' => Str::limit($text, 40)]));
            } else {
                $this->bot(__('Sorry, I couldn’t find “:text”. Please pick one of the options below.', ['text' => Str::limit($text, 40)]), 'error');
            }

            return;
        }

        if ($question->type === Question::TYPE_ATTACHMENTS) {
            $this->bot(__('Please use the 📎 button to add documents or photos, or tap “No, continue”.'), 'error');

            return;
        }

        $this->accept($question, $text);
    }

    private function accept(?Question $question, string $value): void
    {
        if (! $question) {
            return;
        }

        $error = $question->is_system ? $this->setField($question, $value) : $this->setCustom($question, $value);

        if ($error) {
            $this->bot($error, 'error');

            return;
        }

        $this->done($question);
    }

    private function done(Question $question): void
    {
        $this->asked = array_values(array_unique([...$this->asked, $question->key]));
        $this->askNext($this->acknowledge($question));
    }

    private function skipQuestion(Question $question): void
    {
        if ($question->key === 'attachments') {
            $this->attachments = [];
        }

        unset($this->answers[$question->key]);
        $this->done($question);
    }

    /** Ask the first unanswered question, or show the summary once everything is answered. */
    private function askNext(?string $lead = null): void
    {
        $this->mode = self::FILE;

        foreach ($this->questions as $key => $question) {
            if (in_array($key, $this->asked, true)) {
                continue;
            }

            $this->current = $key;
            $this->bot(trim($lead.' '.$this->interpolate((string) $question->localized('question'))), help: $question->localized('help'));

            return;
        }

        $this->current = null;
        $this->mode = self::REVIEW;
        $this->editing = false;
        $this->bot(trim($lead.' '.__('Here is a summary of your complaint. Please check it — tap the pencil to change anything — then submit.')));
    }

    /** Ask a question again after a problem found at submit time. */
    private function reopen(string $key, string $error): void
    {
        $this->editing = true;
        $this->forget($key);
        $this->bot($error, 'error');
        $this->askNext();
    }

    private function forget(string $key): void
    {
        $this->asked = array_values(array_diff($this->asked, [$key]));
    }

    /** Validates one field with the citizen/complaint form rules; returns the error message, if any. */
    private function setField(Question $question, string $value): ?string
    {
        $field = $question->key;

        if (in_array($field, Question::IDENTITY_KEYS, true)) {
            $value = CitizenForm::normalize($field, $value);
            $validator = Validator::make([$field => $value], [$field => CitizenForm::identityRules()[$field]],
                CitizenForm::messageTexts(), CitizenForm::attributeNames());

            if ($validator->fails()) {
                return $validator->errors()->first($field);
            }

            $this->identity[$field] = $value;

            return null;
        }

        if (! property_exists($this->form, $field)) {
            return null; // e.g. "attachments", handled by its own actions
        }

        $value = match ($question->type) {
            Question::TYPE_COURT => (int) $value,
            Question::TYPE_DETAILS => trim($value),
            default => trim(preg_replace('/\s+/u', ' ', $value)),
        };

        $validator = Validator::make([$field => $value], [$field => ComplaintForm::rulesFor()[$field] ?? ['nullable']],
            ComplaintForm::messageTexts(), ComplaintForm::attributeNames());

        if ($validator->fails()) {
            return $validator->errors()->first($field);
        }

        $this->form->{$field} = $value;

        return null;
    }

    private function setCustom(Question $question, string $value): ?string
    {
        if (in_array($question->type, [Question::TYPE_YES_NO, Question::TYPE_CHOICE], true)) {
            $display = $this->optionsFor($question)[$value];
            // Admins read the answers in English where available.
            $answer = $question->type === Question::TYPE_YES_NO
                ? ucfirst($value)
                : ($question->options[(int) $value]['en'] ?? $display);
        } else {
            $max = $question->type === Question::TYPE_LONG_TEXT ? 2000 : 500;
            $validator = Validator::make(['answer' => $value], ['answer' => ['required', 'string', 'max:'.$max]], [], ['answer' => __('answer')]);

            if ($validator->fails()) {
                return $validator->errors()->first('answer');
            }

            $answer = $display = trim($value);
        }

        $this->answers[$question->key] = ['question' => $question->question_en, 'answer' => $answer, 'display' => $display];

        return null;
    }

    /**
     * Option values a typed answer may refer to: an exact name (English or Urdu) or code wins;
     * otherwise every option containing the text.
     *
     * @return list<int|string>
     */
    private function match(Question $question, string $text, array $options): array
    {
        $needle = Str::lower(trim($text));
        $aliases = $this->aliasesFor($question, $options);

        foreach ($aliases as $value => $names) {
            if (in_array($needle, $names, true)) {
                return [$value];
            }
        }

        if (mb_strlen($needle) < 2) {
            return [];
        }

        return array_keys(array_filter($aliases, fn (array $names) => collect($names)->contains(fn ($name) => Str::contains($name, $needle))));
    }

    /** @return array<int|string, list<string>> */
    private function aliasesFor(Question $question, array $options): array
    {
        $lower = fn (array $names) => array_values(array_filter(array_map(fn ($v) => $v ? Str::lower($v) : null, $names)));

        return match ($question->type) {
            Question::TYPE_COUNTRY => collect(Countries::ALL)->map(fn ($row, $code) => $lower([$row[0], $row[1], $code]))->all(),
            Question::TYPE_COURT => $this->courts->mapWithKeys(fn (Court $c) => [$c->id => $lower([
                $c->name_en.', '.$c->district_en, $c->name_ur.'، '.$c->district_ur, $c->label,
            ])])->all(),
            Question::TYPE_CATEGORY => collect(ComplaintCategory::cases())->mapWithKeys(fn (ComplaintCategory $c) => [$c->value => $lower([$c->label(), $c->value, str_replace('_', ' ', $c->value)])])->all(),
            Question::TYPE_YES_NO => ['yes' => ['yes', 'y', 'haan', 'han', 'ji', 'jee', 'ہاں', 'جی', 'جی ہاں'], 'no' => ['no', 'n', 'nahi', 'nahin', 'نہیں', 'جی نہیں']],
            default => collect($options)->map(fn ($label, $value) => $lower([$label, (string) $value]))->all(),
        };
    }

    private function acknowledge(Question $question): string
    {
        if ($this->editing) {
            return __('Updated.');
        }

        if ($question->key === 'name') {
            return __('Thank you, :name.', ['name' => $this->firstName()]);
        }

        $phrases = [__('Got it.'), __('Thanks!'), __('Noted.'), __('Perfect.'), __('Great.')];

        return $phrases[count($this->messages) % count($phrases)];
    }

    // ---------------------------------------------------------------- internals

    /** Continues this session's chat (from another page, or after a while). */
    private function restore(ChatConversation $conversation): bool
    {
        $state = $conversation->state;

        if (! is_array($state) || empty($state['messages']) || $conversation->status === ChatConversation::RESTARTED) {
            return false;
        }

        if ($conversation->status === ChatConversation::COMPLETED && $conversation->completed_at?->lt(now()->subMinutes(self::SHOW_COMPLETED_MINUTES))) {
            return false;
        }

        $this->conversationId = $conversation->id;
        $this->loggedCount = $conversation->message_count;

        foreach (['messages', 'mode', 'current', 'asked', 'identity', 'answers', 'editing', 'trackingNumber', 'trackStep', 'trackRef'] as $property) {
            if (array_key_exists($property, $state)) {
                $this->{$property} = $state[$property];
            }
        }

        $this->form->fill(Arr::only($state['form'] ?? [], ['court_id', 'case_number', 'case_title', 'category', 'description']));
        $this->attachments = array_values(array_filter(array_map(fn ($name) => $this->restoreUpload($name), $state['attachments'] ?? [])));
        $this->restoredCount = count($this->messages);

        // The question may have been removed by the admin since; carry on with the next one.
        if ($this->mode === self::FILE && ! $this->currentQuestion()) {
            $this->askNext();
        } elseif ($this->mode !== self::DONE && $conversation->last_activity_at?->lt(now()->subMinutes(self::WELCOME_BACK_MINUTES))) {
            $this->bot(__('Welcome back!'));

            if ($question = $this->currentQuestion()) {
                $this->bot($this->interpolate((string) $question->localized('question')), help: $question->localized('help'));
            }
        }

        return true;
    }

    /** Everything needed to pick the chat up again (stored encrypted). */
    private function state(): array
    {
        return [
            'messages' => $this->messages,
            'mode' => $this->mode,
            'current' => $this->current,
            'asked' => $this->asked,
            'identity' => $this->identity,
            'answers' => $this->answers,
            'editing' => $this->editing,
            'trackingNumber' => $this->trackingNumber,
            'trackStep' => $this->trackStep,
            'trackRef' => $this->trackRef,
            'form' => Arr::only($this->form->all(), ['court_id', 'case_number', 'case_title', 'category', 'description']),
            'attachments' => array_map(fn (TemporaryUploadedFile $file) => $file->getFilename(), $this->attachments),
        ];
    }

    private function status(): string
    {
        return match (true) {
            (bool) $this->trackingNumber => ChatConversation::COMPLETED,
            $this->mode === self::REVIEW => ChatConversation::REVIEW,
            $this->filingInProgress() || $this->mode === self::FILE => ChatConversation::FILING,
            default => ChatConversation::BROWSING,
        };
    }

    /** Temporary uploads survive a page change for as long as Livewire keeps them (24 hours). */
    private function restoreUpload(mixed $name): ?TemporaryUploadedFile
    {
        if (! is_string($name) || ! preg_match('/^[A-Za-z0-9._=+-]+$/', $name)) {
            return null;
        }

        $file = TemporaryUploadedFile::createFromLivewire($name);

        return $file->exists() ? $file : null;
    }

    /** Rate limit per connection; replies once when it is hit. */
    private function throttled(): bool
    {
        $key = 'chatbot:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ACTIONS_PER_MINUTE)) {
            if (($this->messages[array_key_last($this->messages)]['tone'] ?? null) !== 'limit') {
                $this->bot(__('You are sending messages too quickly. Please wait :seconds seconds and try again.', ['seconds' => RateLimiter::availableIn($key)]), 'limit');
            }

            return true;
        }

        RateLimiter::hit($key, 60);

        return false;
    }

    private function interpolate(string $text): string
    {
        $text = strtr($text, ['{name}' => $this->firstName()]);

        // Tidy up when {name} was empty, e.g. ", which country…" → "Which country…"
        $text = trim(preg_replace(['/\s+([,.!?،۔])/u', '/([,،])\s*([.!?۔])/u', '/^[,،]\s*/u', '/\s{2,}/u'], ['$1', '$2', '', ' '], $text));

        return Str::ucfirst($text);
    }

    private function firstName(): string
    {
        return (string) Str::of($this->identity['name'] ?? '')->trim()->explode(' ')->first();
    }

    private function fieldLabel(string $key): string
    {
        return match ($key) {
            'name' => __('Full name'),
            'country' => __('Country'),
            'phone' => __('WhatsApp number'),
            'cnic_passport' => __('CNIC / passport'),
            'court_id' => __('Court'),
            'case_number' => __('Case number'),
            'case_title' => __('Case title'),
            'category' => __('Category'),
            'description' => __('Description'),
            'attachments' => __('Attachments'),
            default => $key,
        };
    }

    /** @return list<string> */
    private function paragraphs(string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R\s*\R/u', $text)), 'filled'));
    }

    private function bot(string $text, ?string $tone = null, ?string $help = null): void
    {
        $this->messages[] = ['id' => Str::random(10), 'from' => 'bot', 'text' => $text, 'tone' => $tone, 'help' => $help, 'key' => $this->current];
    }

    private function user(string $text): void
    {
        $this->messages[] = ['id' => Str::random(10), 'from' => 'user', 'text' => $text, 'tone' => null, 'help' => null, 'key' => $this->current];
    }
}
