@php
    $helpline = \App\Support\Site::get('helpline');
    $urdu = app()->getLocale() === 'ur';
    $citizen = auth('citizen')->check();
    $links = [
        ['label' => __('Home'), 'href' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => __('How it works'), 'href' => route('home').'#how-it-works', 'active' => false],
        ['label' => __('FAQs'), 'href' => route('home').'#faqs', 'active' => false],
        ['label' => __('Track Complaint'), 'href' => route('complaints.track'), 'active' => request()->routeIs('complaints.track')],
        $citizen
            ? ['label' => __('My complaints'), 'href' => route('citizen.complaints.index'), 'active' => request()->routeIs('citizen.*')]
            : ['label' => __('Login'), 'href' => route('citizen.login'), 'active' => request()->routeIs('citizen.login', 'citizen.register')],
    ];
@endphp

{{-- Utility bar --}}
<div class="on-dark bg-brand-950 text-brand-100">
    <div class="mx-auto flex h-10 max-w-7xl items-center justify-between gap-4 px-4 text-xs sm:px-6 lg:px-8">
        <p class="flex min-w-0 items-center gap-2">
            <x-public.flag class="h-3.5 w-auto shrink-0 rounded-[2px] ring-1 ring-white/20" />
            <span class="truncate">
                <span class="sm:hidden">{{ __('Judiciary of AJ&K') }}</span>
                <span class="hidden sm:inline">{{ __('Lower Judiciary of Azad Jammu & Kashmir · For overseas citizens') }}</span>
            </span>
        </p>
        <div class="flex shrink-0 items-center gap-4">
            <p class="hidden sm:block">
                {{ __('Helpline:') }}
                <a href="{{ \App\Support\Site::tel($helpline) }}" class="font-semibold text-white ltr-nums hover:underline">{{ $helpline }}</a>
            </p>
            <a href="{{ route('locale.switch', $urdu ? 'en' : 'ur') }}" lang="{{ $urdu ? 'en' : 'ur' }}"
               class="rounded px-2 font-semibold text-white hover:bg-white/10 {{ $urdu ? 'font-sans' : 'font-urdu !leading-8' }}">
                {{ $urdu ? 'English' : 'اردو' }}
            </a>
        </div>
    </div>
</div>

{{-- Header --}}
<header x-data="{ open: false }" @keydown.escape.window="open = false"
        class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/85">
    <div class="mx-auto flex h-[84px] max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3 rounded-lg">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-700 shadow-sm ring-1 ring-brand-800/20">
                <svg class="h-6 w-6 text-accent-400" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z"/></svg>
            </span>
            <span class="min-w-0 leading-tight">
                <span class="block truncate font-display text-base font-extrabold text-brand-900 sm:text-lg">{{ __('Overseas Judiciary Complaint Portal') }}</span>
                <span class="block truncate text-xs font-medium text-slate-600 sm:text-sm">{{ __('Lower Judiciary of AJ&K') }}</span>
            </span>
        </a>

        <nav class="hidden items-center gap-1 xl:flex" aria-label="{{ __('Main') }}">
            @foreach ($links as $link)
                <a href="{{ $link['href'] }}" @if ($link['active']) aria-current="page" @endif
                   @class([
                       'flex min-h-11 items-center whitespace-nowrap rounded-full px-4 text-sm font-semibold transition',
                       'bg-brand-100 text-brand-900' => $link['active'],
                       'text-slate-700 hover:bg-slate-100 hover:text-slate-900' => ! $link['active'],
                   ])>{{ $link['label'] }}</a>
            @endforeach
            <x-ui.button :href="route('citizen.complaints.create')" class="ms-2 whitespace-nowrap !rounded-full !px-5">{{ __('File a Complaint') }}</x-ui.button>
        </nav>

        <button type="button" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg text-slate-700 hover:bg-slate-100 xl:hidden"
                @click="open = ! open" :aria-expanded="open.toString()" aria-controls="mobile-nav">
            <span class="sr-only" x-text="open ? @js(__('Close menu')) : @js(__('Open menu'))">{{ __('Open menu') }}</span>
            <svg x-show="! open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <svg x-show="open" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
    </div>

    <nav id="mobile-nav" x-show="open" x-cloak x-transition.origin.top class="border-t border-slate-200 bg-white xl:hidden" aria-label="{{ __('Main') }}">
        <div class="mx-auto max-w-7xl space-y-1 px-4 py-3 sm:px-6">
            @foreach ($links as $link)
                <a href="{{ $link['href'] }}" @click="open = false" @if ($link['active']) aria-current="page" @endif
                   @class([
                       'flex min-h-11 items-center rounded-xl px-4 text-base font-semibold',
                       'bg-brand-100 text-brand-900' => $link['active'],
                       'text-slate-700 hover:bg-slate-100' => ! $link['active'],
                   ])>{{ $link['label'] }}</a>
            @endforeach
            <x-ui.button :href="route('citizen.complaints.create')" class="mt-2 w-full !rounded-xl">{{ __('File a Complaint') }}</x-ui.button>
        </div>
    </nav>
</header>
