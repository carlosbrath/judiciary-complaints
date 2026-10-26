@props(['color' => 'slate'])

@php
    $colors = [
        'slate' => 'bg-slate-100 text-slate-700 ring-slate-500/20',
        'brand' => 'bg-brand-50 text-brand-800 ring-brand-600/20',
        'accent' => 'bg-accent-50 text-accent-800 ring-accent-600/30',
        'blue' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'amber' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'red' => 'bg-red-50 text-red-700 ring-red-600/20',
        'purple' => 'bg-purple-50 text-purple-700 ring-purple-600/20',
    ];
@endphp

<span {{ $attributes->class('inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset '.($colors[$color] ?? $colors['slate'])) }}>
    {{ $slot }}
</span>
