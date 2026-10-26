@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $variants = [
        'primary' => 'bg-brand-700 text-white hover:bg-brand-800 focus-visible:outline-brand-700 shadow-sm',
        'accent' => 'bg-accent-500 text-slate-950 hover:bg-accent-400 focus-visible:outline-accent-600 shadow-sm',
        'secondary' => 'bg-white text-slate-800 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 shadow-sm',
        'ghost' => 'text-slate-700 hover:bg-slate-100',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus-visible:outline-red-600 shadow-sm',
        'light' => 'bg-white text-brand-800 hover:bg-brand-50 shadow-sm',
    ];

    $sizes = [
        'sm' => 'min-h-9 px-3 py-1.5 text-sm gap-1.5',
        'md' => 'min-h-11 px-4 py-2.5 text-sm gap-2', // ≥ 44px touch target
        'lg' => 'min-h-12 px-6 py-3 text-base gap-2',
    ];

    $classes = 'inline-flex items-center justify-center rounded-lg font-semibold transition disabled:cursor-not-allowed disabled:opacity-60 '
        .($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
