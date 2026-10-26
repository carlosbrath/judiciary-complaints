@props(['title' => null, 'description' => null, 'padding' => true])

<section {{ $attributes->class('rounded-[var(--radius-card)] border border-slate-200 bg-white shadow-[var(--shadow-card)]') }}>
    @if ($title || isset($actions))
        <header class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
            <div>
                @if ($title)
                    <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="mt-0.5 text-sm text-slate-500">{{ $description }}</p>
                @endif
            </div>
            {{ $actions ?? '' }}
        </header>
    @endif

    <div @class(['px-5 py-5 sm:px-6' => $padding])>
        {{ $slot }}
    </div>

    @isset($footer)
        <footer class="border-t border-slate-100 bg-slate-50/60 px-5 py-3 sm:px-6">{{ $footer }}</footer>
    @endisset
</section>
