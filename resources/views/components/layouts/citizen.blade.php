{{-- Logged-in citizen area: public chrome plus an account sub-navigation. --}}
@props(['title' => null])

<x-layouts.public :title="$title">
    <div class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-2 px-4 py-2 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-1 overflow-x-auto" aria-label="{{ __('My account') }}">
                @foreach ([['citizen.complaints.index', 'citizen.complaints.index', __('My complaints')], ['citizen.complaints.create', 'citizen.complaints.create', __('File a Complaint')]] as [$pattern, $route, $label])
                    <a href="{{ route($route) }}" @class([
                        'whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium',
                        'bg-brand-50 text-brand-800' => request()->routeIs($pattern),
                        'text-slate-600 hover:bg-slate-100' => ! request()->routeIs($pattern),
                    ])>{{ $label }}</a>
                @endforeach
            </nav>
            <div class="flex items-center gap-3 text-sm">
                <span class="hidden text-slate-500 sm:inline">{{ auth('citizen')->user()?->name }}</span>
                <form method="POST" action="{{ route('citizen.logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg px-3 py-2 font-medium text-red-600 hover:bg-red-50">{{ __('Log out') }}</button>
                </form>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <x-ui.alert type="success" class="mb-6">{{ session('status') }}</x-ui.alert>
        @endif

        {{ $slot }}
    </div>
</x-layouts.public>
