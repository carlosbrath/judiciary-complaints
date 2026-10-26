{{--
    Open with:  $dispatch('open-modal', 'name')   (Alpine)  or  $this->dispatch('open-modal', 'name')  (Livewire)
    Close with: $dispatch('close-modal', 'name')
--}}
@props(['name', 'title' => null, 'maxWidth' => 'lg'])

@php
    $widths = ['sm' => 'sm:max-w-sm', 'md' => 'sm:max-w-md', 'lg' => 'sm:max-w-lg', 'xl' => 'sm:max-w-xl', '2xl' => 'sm:max-w-2xl'];
@endphp

<div x-data="{ show: false, name: @js($name) }"
     x-on:open-modal.window="if ([$event.detail, $event.detail?.[0], $event.detail?.name].includes(name)) show = true"
     x-on:close-modal.window="if ([$event.detail, $event.detail?.[0], $event.detail?.name].includes(name)) show = false"
     x-on:keydown.escape.window="show = false"
     x-show="show" x-cloak
     class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
     role="dialog" aria-modal="true" @if ($title) aria-label="{{ $title }}" @endif>
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/50" @click="show = false"></div>

    <div x-show="show" x-trap.noscroll="show"
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="relative flex max-h-[calc(100dvh-2rem)] w-full {{ $widths[$maxWidth] ?? $widths['lg'] }} flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
        {{-- Header and footer stay put; only the body scrolls when the content is taller than the screen. --}}
        @if ($title)
            <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>
                <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" @click="show = false">
                    <span class="sr-only">{{ __('Close') }}</span>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>
        @endif
        <div class="min-h-0 flex-1 overflow-y-auto px-5 py-5">{{ $slot }}</div>
        @isset($footer)
            <div class="flex shrink-0 justify-end gap-2 border-t border-slate-100 bg-slate-50 px-5 py-3">{{ $footer }}</div>
        @endisset
    </div>
</div>
