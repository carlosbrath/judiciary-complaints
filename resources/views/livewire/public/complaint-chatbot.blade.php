@php
    use App\Livewire\Public\ComplaintChatbot as Bot;
    use App\Models\ChatbotQuestion as Q;

    $type = $question?->type;
    $long = in_array($type, [Q::TYPE_DETAILS, Q::TYPE_LONG_TEXT], true);
    $placeholder = match (true) {
        $mode === Bot::TRACK && $trackStep === 'identity' => '12345-1234567-1 / AB1234567',
        $mode === Bot::TRACK => 'OJC-2026-00001',
        $type === Q::TYPE_ATTACHMENTS => __('Use 📎 to attach files'),
        $type === Q::TYPE_NAME => __('e.g. Ahmed Khan'),
        $type === Q::TYPE_PHONE => '+447700900123',
        $type === Q::TYPE_IDENTITY => '12345-1234567-1 / AB1234567',
        (bool) $options => __('Type to search or choose an option…'),
        in_array($mode, [Bot::MENU, Bot::FAQ, Bot::DONE], true) => __('Type your question…'),
        default => __('Type your answer…'),
    };
    $ltrInput = in_array($type, [Q::TYPE_PHONE, Q::TYPE_IDENTITY], true) || $mode === Bot::TRACK;
    $botName = __('Judiciary Assistant');
    $scales = 'M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z';
    $pill = 'rounded-full border border-slate-300 bg-white px-4 py-1.5 text-sm text-slate-700 shadow-sm transition hover:border-brand-600 hover:bg-brand-50 hover:text-brand-800 disabled:opacity-50';
    $iconButton = 'flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-brand-700';
@endphp

<div @class(['h-full' => $embedded, 'bg-slate-100 sm:px-6 sm:py-8' => ! $embedded])>
    <section x-data="chatbot" aria-label="{{ $botName }}"
             @class([
                 'relative mx-auto flex max-w-3xl flex-col overflow-hidden bg-white',
                 'h-full' => $embedded,
                 'h-[calc(100dvh-125px)] sm:h-[min(820px,calc(100dvh-190px))] sm:rounded-2xl sm:shadow-2xl sm:ring-1 sm:ring-slate-200' => ! $embedded,
             ])>

        {{-- Header --}}
        <header class="relative shrink-0 bg-gradient-to-r from-brand-700 to-brand-600 px-4 py-3 text-white">
            <div class="flex items-center gap-3">
                <span class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/30">
                    <svg class="h-5 w-5 text-accent-300" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $scales }}"/></svg>
                    <span class="absolute -bottom-0.5 -end-0.5 flex h-3.5 w-3.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-300 opacity-60 motion-reduce:hidden"></span>
                        <span class="relative inline-flex h-3.5 w-3.5 rounded-full bg-green-400 ring-2 ring-brand-700"></span>
                    </span>
                </span>
                <div class="min-w-0 flex-1 leading-tight">
                    <p class="truncate text-[17px] font-semibold">{{ $botName }}</p>
                    <p class="text-xs text-brand-50/90">
                        <span x-show="$store.chatbot.typing" x-cloak>{{ __('typing…') }}</span>
                        <span x-show="! $store.chatbot.typing">{{ __('Online · replies instantly') }}</span>
                    </p>
                </div>

                <div class="flex items-center">
                    @if (count($messages) > 4)
                        <div x-data="{ confirm: false }" class="flex items-center">
                            <span x-show="confirm" x-cloak x-transition class="me-1 text-xs text-brand-50">{{ __('Start over?') }}</span>
                            <button type="button" @click="confirm ? ($wire.restart(), confirm = false) : (confirm = true, setTimeout(() => confirm = false, 3000))"
                                    :class="confirm && 'bg-white/20'" class="rounded-lg p-2 text-white/90 transition hover:bg-white/15 hover:text-white" title="{{ __('Start over') }}">
                                <span class="sr-only">{{ __('Start over') }}</span>
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                            </button>
                        </div>
                    @endif
                    @if ($embedded)
                        <a href="{{ route('chatbot') }}" class="hidden rounded-lg p-2 text-white/90 transition hover:bg-white/15 hover:text-white sm:block" title="{{ __('Open full screen') }}">
                            <span class="sr-only">{{ __('Open full screen') }}</span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/></svg>
                        </a>
                        <button type="button" @click="$dispatch('close-chat')" class="rounded-lg p-2 text-white/90 transition hover:bg-white/15 hover:text-white" title="{{ __('Minimise') }}">
                            <span class="sr-only">{{ __('Minimise') }}</span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" d="M5 12h14"/></svg>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Filing progress --}}
            @if (in_array($mode, [Bot::FILE, Bot::REVIEW, Bot::DONE], true))
                <div class="absolute inset-x-0 bottom-0 h-[3px] bg-black/10" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $this->progress() }}" aria-label="{{ __('Progress') }}">
                    <div class="h-full rounded-e-full bg-accent-400 transition-all duration-700 motion-reduce:transition-none" style="width: {{ $this->progress() }}%"></div>
                </div>
            @endif
        </header>

        {{-- Conversation --}}
        <div x-ref="scroller" role="log" aria-live="polite" class="min-h-0 flex-1 space-y-2.5 overflow-y-auto overscroll-contain bg-white px-4 py-4 [scrollbar-width:thin]">
            <p class="pb-1 text-center text-[11px] text-slate-400">
                {{ __('Today') }} · <span class="ltr-nums">{{ now()->format('g:i A') }}</span><br>
                {{ __('An automated assistant — no human is reading this chat.') }}
            </p>

            @foreach ($messages as $message)
                @if ($message['from'] === 'bot')
                    @php($error = in_array($message['tone'], ['error', 'limit'], true))
                    <div wire:key="m-{{ $message['id'] }}" x-data="botMessage(@js($message['text']), @js($loop->index >= $restoredCount))" x-show="shown" x-cloak
                         x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                         class="flex items-end gap-2 pe-8">
                        <span class="mb-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-600 text-white">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $scales }}"/></svg>
                        </span>
                        <div @class([
                            'min-w-0 break-words rounded-2xl rounded-es-md px-3.5 py-2.5 text-[15px] leading-relaxed',
                            'bg-slate-100 text-slate-800' => ! $message['tone'],
                            'bg-red-50 text-red-800 ring-1 ring-red-200' => $error,
                            'bg-brand-50 text-brand-900 ring-1 ring-brand-200' => $message['tone'] === 'success',
                        ])>
                            <span class="sr-only">{{ $message['text'] }}</span>
                            <span aria-hidden="true" class="whitespace-pre-line" dir="auto">@if ($error)⚠️ @endif<span x-text="out"></span></span>
                            @if ($message['help'])
                                <p x-show="done" x-transition.opacity class="mt-1 text-xs text-slate-500">💡 {{ $message['help'] }}</p>
                            @endif
                        </div>
                    </div>
                @else
                    <div wire:key="m-{{ $message['id'] }}" class="flex justify-end ps-12">
                        <p class="whitespace-pre-line break-words rounded-2xl rounded-ee-md bg-brand-600 px-3.5 py-2.5 text-[15px] leading-relaxed text-white" dir="auto">{{ $message['text'] }}</p>
                    </div>
                @endif
            @endforeach

            {{-- Typing indicator --}}
            <div x-show="$store.chatbot.typing" x-cloak class="flex items-end gap-2" aria-hidden="true">
                <span class="mb-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-600 text-white">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $scales }}"/></svg>
                </span>
                <div class="flex gap-1 rounded-2xl rounded-es-md bg-slate-100 px-4 py-3.5">
                    <span class="h-2 w-2 animate-bounce rounded-full bg-slate-400 [animation-delay:-0.3s] motion-reduce:animate-none"></span>
                    <span class="h-2 w-2 animate-bounce rounded-full bg-slate-400 [animation-delay:-0.15s] motion-reduce:animate-none"></span>
                    <span class="h-2 w-2 animate-bounce rounded-full bg-slate-400 motion-reduce:animate-none"></span>
                </div>
            </div>

            {{-- Quick replies (right-aligned, like the citizen's own answer); typing narrows them --}}
            @if (! in_array($mode, [Bot::REVIEW, Bot::DONE], true))
                <div x-show="! $store.chatbot.pending" x-cloak x-transition.opacity wire:key="replies-{{ $mode }}-{{ count($messages) }}"
                     class="flex flex-wrap justify-end gap-2 ps-8 pt-1">
                    @foreach ($options as $value => $label)
                        <button type="button" wire:click="choose(@js((string) $value))" wire:loading.attr="disabled"
                                x-show="! filter || @js(Str::lower($label)).includes(filter.toLowerCase())"
                                @class([$pill, '!border-brand-600 !bg-brand-600 !text-white hover:!bg-brand-700' => $mode === Bot::MENU && $loop->first])
                                dir="auto">{{ $label }}</button>
                    @endforeach

                    @if ($hint !== '')
                        <button type="button" wire:click="clearHint" class="{{ $pill }} text-slate-500">{{ __('Show all') }}</button>
                    @endif

                    @if ($type === Q::TYPE_ATTACHMENTS)
                        @foreach ($attachments as $index => $file)
                            <span wire:key="att-{{ $index }}-{{ $file->getFilename() }}" class="inline-flex max-w-full items-center gap-2 rounded-full bg-brand-50 py-1 pe-1.5 ps-3 text-sm text-brand-900 ring-1 ring-brand-200">
                                📎 <span class="max-w-40 truncate" dir="auto">{{ $file->getClientOriginalName() }}</span>
                                <button type="button" wire:click="removeAttachment({{ $index }})" class="rounded-full p-0.5 text-brand-700 hover:bg-white hover:text-red-600">
                                    <span class="sr-only">{{ __('Remove') }}</span>
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                                </button>
                            </span>
                        @endforeach
                        <span class="basis-full"></span>
                        <button type="button" @click="$dispatch('chat-attach')" class="{{ $pill }}">📎 {{ $attachments ? __('Add more') : __('Attach files') }}</button>
                        @if ($attachments)
                            <button type="button" wire:click="attachmentsDone" class="{{ $pill }} !border-brand-600 !bg-brand-600 !text-white hover:!bg-brand-700">{{ __('Done, continue') }}</button>
                        @endif
                    @endif

                    @if ($question && ! $question->is_required)
                        <button type="button" wire:click="skip" class="{{ $pill }} text-slate-500">
                            {{ $type === Q::TYPE_ATTACHMENTS ? __('No, continue') : __('Skip') }}
                        </button>
                    @endif

                    @if ($mode !== Bot::MENU)
                        <button type="button" wire:click="menu" class="{{ $pill }} border-dashed text-slate-500">☰ {{ __('Main menu') }}</button>
                    @endif
                </div>

                @if ($type === Q::TYPE_ATTACHMENTS)
                    <div class="ps-9 text-end text-[11px] text-slate-400">
                        @error('attachments') <p class="text-red-600">{{ $message }}</p> @enderror
                        @error('pendingAttachments.*') <p class="text-red-600">{{ $message }}</p> @enderror
                        {{ __('PDF or images, up to :size MB each, maximum :count files.', ['size' => $limits['max_file_kb'] / 1024, 'count' => $limits['max_files']]) }}
                    </div>
                @endif
            @endif

            {{-- Summary --}}
            @if ($mode === Bot::REVIEW)
                <div x-show="! $store.chatbot.pending" x-cloak x-transition.opacity class="ms-9 overflow-hidden rounded-2xl ring-1 ring-slate-200">
                    <p class="bg-slate-50 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Your complaint') }}</p>
                    <dl class="divide-y divide-slate-100 text-sm">
                        @foreach ($this->summary() as $row)
                            <div wire:key="sum-{{ $row['key'] }}" class="flex items-start gap-3 px-4 py-2">
                                <dt class="w-24 shrink-0 text-xs font-medium text-slate-500 sm:w-32">{{ $row['label'] }}</dt>
                                <dd class="min-w-0 flex-1 whitespace-pre-line break-words text-slate-900" dir="auto">{{ $row['value'] }}</dd>
                                <button type="button" wire:click="edit(@js($row['key']))" class="-me-1 shrink-0 rounded-lg p-1 text-slate-400 hover:bg-brand-50 hover:text-brand-700">
                                    <span class="sr-only">{{ __('Edit') }} {{ $row['label'] }}</span>
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                                </button>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif

            {{-- Registered --}}
            @if ($mode === Bot::DONE && $trackingNumber)
                <div x-show="! $store.chatbot.pending" x-cloak x-transition.opacity x-data="copyText(@js($trackingNumber))"
                     class="ms-9 rounded-2xl border-2 border-dashed border-brand-300 bg-brand-50/50 p-4 text-center">
                    <p class="text-xs font-semibold uppercase tracking-wider text-brand-800">{{ __('Your tracking number') }}</p>
                    <p class="mt-1.5 break-all font-mono text-xl font-bold tracking-wide text-brand-900 ltr-nums">{{ $trackingNumber }}</p>
                    <div class="mt-3 flex flex-wrap justify-center gap-2">
                        <button type="button" @click="copy()" class="{{ $pill }}" x-text="copied ? @js(__('Copied')) : @js(__('Copy'))">{{ __('Copy') }}</button>
                        @if ($this->citizen())
                            <a href="{{ route('citizen.complaints.show', $trackingNumber) }}" class="{{ $pill }}">{{ __('Open in my complaints') }}</a>
                        @else
                            <a href="{{ route('citizen.register') }}" class="{{ $pill }}">{{ __('Create an account') }}</a>
                        @endif
                        <button type="button" wire:click="restart" class="{{ $pill }}">{{ __('File another complaint') }}</button>
                    </div>
                </div>
            @endif
        </div>

        {{-- Composer --}}
        <footer class="shrink-0 border-t border-slate-200 bg-white transition focus-within:border-brand-300 focus-within:shadow-[0_-4px_16px_-8px_rgb(0_143_69/0.25)]">
            {{-- Honeypot: hidden from people and screen readers --}}
            <div class="hidden" aria-hidden="true"><input type="text" wire:model="form.website" tabindex="-1" autocomplete="off"></div>

            @if ($mode === Bot::REVIEW)
                <div x-show="! $store.chatbot.pending" x-cloak class="space-y-3 p-4">
                    <label class="flex items-start gap-3 text-sm text-slate-700">
                        <input type="checkbox" wire:model="form.declaration" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-600">
                        <span>{{ __('I confirm that the information I have given is true to the best of my knowledge.') }}</span>
                    </label>
                    @error('form.declaration') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    @error('submit') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    @error('attachments.*') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    <div class="flex gap-2">
                        <button type="button" wire:click="menu" class="rounded-xl px-3 py-3 text-sm font-medium text-slate-600 ring-1 ring-slate-300 hover:bg-slate-50">☰</button>
                        <button type="button" wire:click="submit" wire:loading.attr="disabled" wire:target="submit"
                                class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:opacity-60">
                            <svg wire:loading wire:target="submit" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                            <span wire:loading.remove wire:target="submit">{{ __('Submit complaint') }}</span>
                            <span wire:loading wire:target="submit">{{ __('Submitting…') }}</span>
                        </button>
                    </div>
                </div>
            @else
                <div wire:key="composer-{{ $mode }}-{{ $question?->key }}-{{ count($messages) }}">
                    {{-- Sending waits until the bot has finished "typing" (checked here rather than by disabling the button, which clashes with wire:loading). --}}
                    <form x-on:submit.prevent="$store.chatbot.pending || $wire.send()" :class="$store.chatbot.pending && 'opacity-60'" class="transition">
                        <label for="chat-reply" class="sr-only">{{ __('Your message') }}</label>
                        @if ($long)
                            <textarea id="chat-reply" wire:model="reply" rows="2" maxlength="{{ $type === Q::TYPE_DETAILS ? 5000 : 2000 }}" dir="auto"
                                      x-init="filter = ''; $nextTick(() => window.innerWidth > 640 && $el.focus())"
                                      x-on:input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 150) + 'px'"
                                      @keydown.enter="if (! $event.shiftKey) { $event.preventDefault(); $el.form.requestSubmit() }"
                                      placeholder="{{ $placeholder }}"
                                      class="block max-h-[150px] w-full resize-none border-0 bg-transparent px-4 pt-3.5 text-[15px] text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0 focus-visible:outline-none"></textarea>
                        @else
                            <input id="chat-reply" wire:model="reply" autocomplete="off" dir="{{ $ltrInput ? 'ltr' : 'auto' }}"
                                   type="{{ $type === Q::TYPE_PHONE ? 'tel' : 'text' }}"
                                   x-init="filter = ''; $nextTick(() => window.innerWidth > 640 && $el.focus())"
                                   x-on:input="filter = $el.value"
                                   @disabled($type === Q::TYPE_ATTACHMENTS)
                                   maxlength="{{ $question?->key === 'case_title' ? 200 : 255 }}"
                                   placeholder="{{ $placeholder }}"
                                   @class(['block w-full border-0 bg-transparent px-4 pt-3.5 text-[15px] text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0 focus-visible:outline-none disabled:cursor-not-allowed', 'ltr-nums' => $ltrInput])>
                        @endif

                        {{-- Toolbar --}}
                        <div class="flex items-center gap-1 px-2 pb-2 pt-1">
                            @if ($type === Q::TYPE_ATTACHMENTS)
                                <div x-data="dropzone()" @chat-attach.window="pick()">
                                    <input x-ref="input" type="file" multiple class="sr-only" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/*"
                                           @change="handle($event.target.files)" aria-label="{{ __('Choose files') }}">
                                    <button type="button" @click="pick()" class="{{ $iconButton }} text-brand-700" title="{{ __('Attach files') }}">
                                        <span class="sr-only">{{ __('Attach files') }}</span>
                                        <svg x-show="! uploading" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/></svg>
                                        <span x-show="uploading" x-cloak class="text-[10px] font-semibold ltr-nums" x-text="progress + '%'"></span>
                                    </button>
                                </div>
                            @endif

                            @if ($long)
                                <span class="ms-1 hidden text-[11px] text-slate-400 sm:inline">{{ __('Shift + Enter for a new line') }}</span>
                            @endif

                            <button type="submit" wire:loading.attr="disabled" wire:target="send" @disabled($type === Q::TYPE_ATTACHMENTS)
                                    :aria-disabled="$store.chatbot.pending.toString()"
                                    class="ms-auto flex h-10 w-10 items-center justify-center rounded-full bg-brand-600 text-white shadow-sm transition hover:bg-brand-700 disabled:bg-slate-200 disabled:text-slate-400 disabled:shadow-none">
                                <span class="sr-only">{{ __('Send') }}</span>
                                <svg class="h-5 w-5 rtl:-scale-x-100" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </footer>
    </section>
</div>
