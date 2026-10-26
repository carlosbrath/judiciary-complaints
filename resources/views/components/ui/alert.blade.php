@props(['type' => 'info', 'title' => null])

@php
    $styles = [
        'info' => ['box' => 'border-blue-200 bg-blue-50 text-blue-900', 'icon' => 'text-blue-600', 'path' => 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z'],
        'success' => ['box' => 'border-brand-200 bg-brand-50 text-brand-900', 'icon' => 'text-brand-600', 'path' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        'warning' => ['box' => 'border-accent-200 bg-accent-50 text-accent-900', 'icon' => 'text-accent-600', 'path' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z'],
        'error' => ['box' => 'border-red-200 bg-red-50 text-red-900', 'icon' => 'text-red-600', 'path' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'],
    ];
    $style = $styles[$type] ?? $styles['info'];
@endphp

<div role="{{ $type === 'error' ? 'alert' : 'status' }}" {{ $attributes->class('flex gap-3 rounded-xl border p-4 text-sm '.$style['box']) }}>
    <svg class="mt-0.5 h-5 w-5 shrink-0 {{ $style['icon'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $style['path'] }}"/>
    </svg>
    <div class="min-w-0">
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        <div @class(['mt-1' => $title])>{{ $slot }}</div>
    </div>
</div>
