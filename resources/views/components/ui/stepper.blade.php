{{-- $steps: list of labels; $current: 1-based index of the active step. --}}
@props(['steps' => [], 'current' => 1])

<ol {{ $attributes->class('flex items-center gap-2 sm:gap-4') }}>
    @foreach ($steps as $i => $label)
        @php($n = $i + 1)
        <li @class(['flex items-center gap-2 sm:gap-4', 'flex-1' => ! $loop->last])>
            <span class="flex items-center gap-2">
                <span @class([
                    'flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-semibold ltr-nums',
                    'bg-brand-700 text-white' => $n < $current,
                    'bg-brand-700 text-white ring-4 ring-brand-100' => $n === $current,
                    'bg-slate-200 text-slate-600' => $n > $current,
                ]) @if ($n === $current) aria-current="step" @endif>
                    @if ($n < $current)
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    @else
                        {{ $n }}
                    @endif
                </span>
                <span @class([
                    'hidden text-sm font-medium sm:inline',
                    'text-slate-900' => $n <= $current,
                    'text-slate-500' => $n > $current,
                ])>{{ $label }}</span>
            </span>
            @unless ($loop->last)
                <span @class(['h-0.5 flex-1 rounded', 'bg-brand-600' => $n < $current, 'bg-slate-200' => $n >= $current])></span>
            @endunless
        </li>
    @endforeach
</ol>
