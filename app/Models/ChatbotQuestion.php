<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * One question the chatbot asks when filing a complaint. Core questions (is_system) fill a citizen
 * or complaint field named by `key` and can be reworded, reordered and — when optional — switched
 * off. Custom questions are added by the admin; their answers are appended to the description.
 */
class ChatbotQuestion extends Model
{
    // Core answer types (one per field).
    public const TYPE_NAME = 'name';
    public const TYPE_COUNTRY = 'country';
    public const TYPE_PHONE = 'phone';
    public const TYPE_IDENTITY = 'identity';
    public const TYPE_COURT = 'court';
    public const TYPE_CATEGORY = 'category';
    public const TYPE_DETAILS = 'details';
    public const TYPE_ATTACHMENTS = 'attachments';

    // Answer types available to custom questions (core case number/title are short text too).
    public const TYPE_TEXT = 'text';
    public const TYPE_LONG_TEXT = 'long_text';
    public const TYPE_YES_NO = 'yes_no';
    public const TYPE_CHOICE = 'choice';

    public const CUSTOM_TYPES = [
        self::TYPE_TEXT => 'Short text',
        self::TYPE_LONG_TEXT => 'Long text',
        self::TYPE_YES_NO => 'Yes / No',
        self::TYPE_CHOICE => 'Pick from a list',
    ];

    public const TYPE_LABELS = self::CUSTOM_TYPES + [
        self::TYPE_NAME => 'Name',
        self::TYPE_COUNTRY => 'Country list',
        self::TYPE_PHONE => 'WhatsApp number',
        self::TYPE_IDENTITY => 'CNIC / passport',
        self::TYPE_COURT => 'Court list',
        self::TYPE_CATEGORY => 'Category list',
        self::TYPE_DETAILS => 'Description',
        self::TYPE_ATTACHMENTS => 'File upload',
    ];

    /** Keys answered from the citizen's account when they are logged in. */
    public const IDENTITY_KEYS = ['name', 'country', 'phone', 'cnic_passport'];

    protected $fillable = ['key', 'type', 'question_en', 'question_ur', 'help_en', 'help_ur', 'options', 'is_required', 'is_active', 'is_system', 'sort_order'];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    /**
     * Core questions with their default wording, in the default order.
     *
     * @return list<array<string, mixed>>
     */
    public static function defaults(): array
    {
        return [
            ['key' => 'name', 'type' => self::TYPE_NAME, 'is_required' => true,
                'question_en' => "Let's start with your full name.", 'question_ur' => 'سب سے پہلے اپنا پورا نام بتائیں۔'],
            ['key' => 'country', 'type' => self::TYPE_COUNTRY, 'is_required' => true,
                'question_en' => 'Which country do you live in, {name}?', 'question_ur' => '{name}، آپ کس ملک میں رہتے ہیں؟',
                'help_en' => 'Type to search, or pick one below.', 'help_ur' => 'تلاش کے لیے لکھیں یا نیچے سے منتخب کریں۔'],
            ['key' => 'phone', 'type' => self::TYPE_PHONE, 'is_required' => true,
                'question_en' => 'What is your WhatsApp number?', 'question_ur' => 'آپ کا واٹس ایپ نمبر کیا ہے؟',
                'help_en' => 'Include the country code, e.g. +447700900123.', 'help_ur' => 'ملک کے کوڈ کے ساتھ لکھیں، مثلاً ‎+447700900123‎۔'],
            ['key' => 'cnic_passport', 'type' => self::TYPE_IDENTITY, 'is_required' => true,
                'question_en' => 'What is your CNIC/NICOP or passport number?', 'question_ur' => 'آپ کا شناختی کارڈ / نائیکوپ یا پاسپورٹ نمبر کیا ہے؟',
                'help_en' => 'Your complaint is registered against this number. You will need it to track the complaint.', 'help_ur' => 'آپ کی شکایت اسی نمبر پر درج ہوگی۔ شکایت ٹریک کرنے کے لیے یہ نمبر درکار ہوگا۔'],
            ['key' => 'court_id', 'type' => self::TYPE_COURT, 'is_required' => true,
                'question_en' => 'Which court is the case in?', 'question_ur' => 'مقدمہ کس عدالت میں ہے؟',
                'help_en' => 'Type the district or court name to narrow the list.', 'help_ur' => 'فہرست مختصر کرنے کے لیے ضلع یا عدالت کا نام لکھیں۔'],
            ['key' => 'case_number', 'type' => self::TYPE_TEXT, 'is_required' => true,
                'question_en' => 'What is the case number?', 'question_ur' => 'مقدمہ نمبر کیا ہے؟',
                'help_en' => 'As written on the court file or summons, e.g. 123/2025.', 'help_ur' => 'جیسا کہ عدالتی فائل یا سمن پر لکھا ہے، مثلاً 123/2025۔'],
            ['key' => 'case_title', 'type' => self::TYPE_TEXT, 'is_required' => true,
                'question_en' => 'And the case title?', 'question_ur' => 'اور مقدمے کا عنوان؟',
                'help_en' => 'For example: "Ahmed Khan vs. Muhammad Aslam".', 'help_ur' => 'مثلاً: "احمد خان بنام محمد اسلم"۔'],
            ['key' => 'category', 'type' => self::TYPE_CATEGORY, 'is_required' => true,
                'question_en' => 'What kind of problem did you face?', 'question_ur' => 'آپ کو کس قسم کا مسئلہ پیش آیا؟'],
            ['key' => 'description', 'type' => self::TYPE_DETAILS, 'is_required' => true,
                'question_en' => 'Please describe the problem — what happened, when, and what you would like done.', 'question_ur' => 'براہ کرم مسئلہ تفصیل سے بتائیں — کیا ہوا، کب ہوا، اور آپ کیا چاہتے ہیں۔'],
            ['key' => 'attachments', 'type' => self::TYPE_ATTACHMENTS, 'is_required' => false,
                'question_en' => 'Do you have any documents or photos to attach?', 'question_ur' => 'کیا آپ کوئی دستاویز یا تصویر لگانا چاہیں گے؟'],
        ];
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    /** Questions the chatbot asks, in order. Required core questions are never skipped, even if switched off. */
    public static function forChat(): Collection
    {
        return static::query()
            ->where(fn (Builder $q) => $q->where('is_active', true)->orWhere(fn (Builder $q) => $q->where('is_system', true)->where('is_required', true)))
            ->ordered()
            ->get()
            ->keyBy('key');
    }

    public function isCustom(): bool
    {
        return ! $this->is_system;
    }

    /** Core optional questions may be switched off; core required ones may not. */
    public function canBeDeactivated(): bool
    {
        return ! ($this->is_system && $this->is_required);
    }

    public function localized(string $attribute): ?string
    {
        $ur = $this->{$attribute.'_ur'};

        return app()->getLocale() === 'ur' && filled($ur) ? $ur : $this->{$attribute.'_en'};
    }

    /** Options of a custom "choice" question as [index => label in the current locale]. */
    public function choiceLabels(): array
    {
        return collect($this->options ?? [])
            ->map(fn (array $o) => app()->getLocale() === 'ur' && filled($o['ur'] ?? null) ? $o['ur'] : $o['en'])
            ->all();
    }
}
