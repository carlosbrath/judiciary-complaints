@props(['title' => null])

@php($user = auth()->user())

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    @include('partials.head', ['title' => $title])
</head>
<body x-data="{ sidebar: false }" class="min-h-screen bg-slate-100">
    {{-- Mobile backdrop --}}
    <div x-show="sidebar" x-cloak x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden" @click="sidebar = false"></div>

    <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 start-0 z-50 flex w-64 -translate-x-full flex-col bg-brand-950 text-brand-100 transition-transform lg:translate-x-0">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 border-b border-brand-900 px-5 py-4">
            <img src="{{ asset('images/logo.png') }}" alt="" class="h-9 w-9 rounded-full bg-white p-0.5">
            <div class="leading-tight">
                <p class="text-sm font-bold text-white">Overseas Complaints</p>
                <p class="text-xs text-brand-300">Admin Panel</p>
            </div>
        </a>

        <nav class="flex-1 overflow-y-auto px-3 py-4" aria-label="Admin">
            <x-admin.sidebar />
        </nav>
    </aside>

    <div class="lg:ps-64">
        <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-slate-200 bg-white px-4 sm:px-6">
            <button type="button" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden" @click="sidebar = true">
                <span class="sr-only">Open sidebar</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <h1 class="min-w-0 flex-1 truncate text-lg font-semibold text-slate-900">{{ $title }}</h1>

            {{-- User menu --}}
            <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                <button type="button" @click="open = ! open" class="flex items-center gap-3 rounded-lg p-1 pe-2 hover:bg-slate-100" :aria-expanded="open">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-700 text-sm font-semibold text-white">
                        {{ Str::of($user->name)->explode(' ')->take(2)->map(fn ($w) => Str::substr($w, 0, 1))->implode('') }}
                    </span>
                    <span class="hidden text-start leading-tight sm:block">
                        <span class="block text-sm font-medium text-slate-900">{{ $user->name }}</span>
                        <span class="block text-xs text-slate-500">Admin</span>
                    </span>
                </button>
                <div x-show="open" x-cloak x-transition class="absolute end-0 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">
                    <div class="border-b border-slate-100 px-4 py-3 text-sm">
                        <p class="font-medium text-slate-900">{{ $user->name }}</p>
                        <p class="truncate text-slate-500">{{ $user->email }}</p>
                    </div>
                    <a href="{{ route('home') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">View public site</a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-2 text-start text-sm text-red-600 hover:bg-red-50">Log out</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <x-ui.alert type="success" class="mb-6">{{ session('status') }}</x-ui.alert>
            @endif

            {{ $slot }}
        </main>
    </div>

    {{-- Toasts: Livewire actions call $this->dispatch('notify', message: '…', type: 'success'|'error'). --}}
    <div x-data="{ toasts: [] }"
         x-on:notify.window="const id = Date.now() + Math.random();
                             toasts.push({ id, message: $event.detail.message, type: $event.detail.type ?? 'success' });
                             setTimeout(() => toasts = toasts.filter(t => t.id !== id), 5000)"
         class="pointer-events-none fixed bottom-4 end-4 z-[60] flex w-full max-w-sm flex-col gap-2 px-4 sm:px-0" aria-live="polite">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-transition class="pointer-events-auto flex items-start gap-3 rounded-xl border p-4 text-sm shadow-lg"
                 :class="toast.type === 'error' ? 'border-red-200 bg-red-50 text-red-900' : 'border-brand-200 bg-white text-slate-800'"
                 :role="toast.type === 'error' ? 'alert' : 'status'">
                <span class="mt-0.5 h-2.5 w-2.5 shrink-0 rounded-full" :class="toast.type === 'error' ? 'bg-red-500' : 'bg-brand-500'"></span>
                <p class="flex-1" x-text="toast.message"></p>
                <button type="button" class="text-slate-400 hover:text-slate-600" @click="toasts = toasts.filter(t => t.id !== toast.id)">
                    <span class="sr-only">Dismiss</span>✕
                </button>
            </div>
        </template>
    </div>
</body>
</html>
