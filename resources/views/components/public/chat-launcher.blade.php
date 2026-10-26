{{--
    Floating chat button + window on every public page. The chatbot loads when the window is first
    opened; its conversation is kept in the session, and an open window reopens on the next page
    (desktop only — on phones it covers the whole page).
--}}
@php($botName = __('Judiciary Assistant'))

<div x-data="chatLauncher"
     @close-chat.window="open = false"
     @open-chat.window="open = true"
     @keydown.escape.window="open = false">

    {{-- Chat window: full screen on phones, floating panel on larger screens --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none" x-transition:enter-start="opacity-0 translate-y-6 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-6"
         class="fixed inset-0 z-50 origin-bottom-right overflow-hidden bg-white shadow-[0_24px_60px_-12px_rgb(15_23_42/0.35)] sm:inset-auto sm:bottom-28 sm:end-6 sm:h-[min(640px,calc(100dvh-9rem))] sm:w-[390px] sm:rounded-2xl sm:ring-1 sm:ring-slate-900/10 rtl:origin-bottom-left"
         role="dialog" aria-label="{{ $botName }}">
        <livewire:public.complaint-chatbot :embedded="true" lazy />

    </div>

    {{-- Welcome teaser --}}
    <div x-show="teaser && ! open" x-cloak
         x-transition:enter="transition ease-out duration-300 motion-reduce:transition-none" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
         class="fixed bottom-[6.5rem] end-5 z-40 w-64 rounded-2xl rounded-ee-sm bg-white p-4 pe-9 text-sm text-slate-700 shadow-xl ring-1 ring-slate-900/5 sm:end-6">
        <button type="button" @click="dismissTeaser()"
                class="absolute end-2 top-2 rounded-full p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
            <span class="sr-only">{{ __('Dismiss') }}</span>
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
        <button type="button" @click="open = true" class="text-start">
            <span class="block font-semibold text-slate-900">👋 {{ __('Assalam-o-Alaikum! Need help with a court case?') }}</span>
            <span class="mt-1 block text-slate-600">{{ __('Ask :name — file a complaint or check its status in minutes.', ['name' => $botName]) }}</span>
        </button>
    </div>

    {{-- Launcher: speech-bubble shape (square corner towards the screen edge) with icon + "Chat" --}}
    <button type="button" @click="open = ! open"
            :class="open ? 'hidden sm:flex' : 'flex'"
            class="group fixed bottom-5 end-5 z-50 h-[68px] w-[68px] flex-col items-center justify-center gap-0.5 rounded-[64px_64px_0] bg-brand-600 text-white shadow-[0_10px_30px_-6px_rgb(0_112_56/0.6)] ring-4 ring-white transition duration-200 hover:-translate-y-0.5 hover:bg-brand-700 focus-visible:outline-offset-4 motion-reduce:transition-none motion-reduce:hover:translate-y-0 sm:bottom-6 sm:end-6 rtl:rounded-[64px_64px_64px_0]"
            :aria-expanded="open.toString()">
        {{-- "Active" pulse until the chat has been opened --}}
        <span x-show="! started" class="pointer-events-none absolute inset-0 -z-10 animate-ping rounded-[64px_64px_0] bg-brand-500/40 [animation-duration:2.2s] motion-reduce:hidden rtl:rounded-[64px_64px_64px_0]"></span>

        <span class="sr-only" x-text="open ? @js(__('Minimise chat')) : @js(__('Chat with :name', ['name' => $botName]))"></span>
        <svg x-show="! open" class="h-7 w-7 transition group-hover:scale-110" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M4.848 2.771A49.144 49.144 0 0112 2.25c2.43 0 4.817.178 7.152.52 1.978.292 3.348 2.024 3.348 3.97v6.02c0 1.946-1.37 3.678-3.348 3.97a48.901 48.901 0 01-3.476.383.39.39 0 00-.297.17l-2.755 4.133a.75.75 0 01-1.248 0l-2.755-4.133a.39.39 0 00-.297-.17 48.9 48.9 0 01-3.476-.384c-1.978-.29-3.348-2.024-3.348-3.97V6.741c0-1.946 1.37-3.68 3.348-3.97zM6.75 8.25a.75.75 0 01.75-.75h9a.75.75 0 010 1.5h-9a.75.75 0 01-.75-.75zm.75 2.25a.75.75 0 000 1.5H12a.75.75 0 000-1.5H7.5z" clip-rule="evenodd"/>
        </svg>
        <svg x-show="open" x-cloak class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
        <span class="text-[13px] font-semibold leading-none" x-text="open ? @js(__('Close')) : @js(__('Chat'))">{{ __('Chat') }}</span>

        {{-- Unread dot --}}
        <span x-show="! started" class="absolute -end-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-accent-500 text-[10px] font-bold text-slate-950 ring-2 ring-white">1</span>
    </button>
</div>
