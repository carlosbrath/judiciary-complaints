@props(['title', 'description' => null])

<div {{ $attributes->class('mb-6 flex flex-wrap items-end justify-between gap-4') }}>
    <div>
        <h2 class="text-xl font-bold text-slate-900">{{ $title }}</h2>
        @if ($description)
            <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
