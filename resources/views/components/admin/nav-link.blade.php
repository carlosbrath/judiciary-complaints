@props(['href' => '#', 'active' => false, 'icon' => null, 'badge' => null])

<a href="{{ $href }}" @if ($active) aria-current="page" @endif
   {{ $attributes->class([
       'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
       'bg-brand-800 text-white' => $active,
       'text-brand-100 hover:bg-brand-900 hover:text-white' => ! $active,
   ]) }}>
    @if ($icon)
        <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
        </svg>
    @endif
    <span class="flex-1 truncate">{{ $slot }}</span>
    @if ($badge)
        <span class="rounded-full bg-accent-500 px-2 py-0.5 text-xs font-bold text-slate-950">{{ $badge }}</span>
    @endif
</a>
