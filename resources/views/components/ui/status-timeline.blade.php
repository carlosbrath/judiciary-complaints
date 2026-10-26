{{--
    Submitted → Under Review → In Progress → Resolved/Closed for one complaint.
    <x-ui.status-timeline :complaint="$complaint" />
--}}
@props(['complaint'])

@php
    use App\Enums\ComplaintStatus;

    $status = $complaint->status;
    $current = $status->step();
    // When a step was reached: first review that moved the complaint to that step.
    $reached = $complaint->reviews->filter->changesStatus()->groupBy(fn ($r) => $r->status_to->step())->map->first();
    $steps = [
        1 => [ComplaintStatus::Submitted->label(), $complaint->created_at],
        2 => [ComplaintStatus::UnderReview->label(), $reached->get(2)?->created_at],
        3 => [ComplaintStatus::InProgress->label(), $reached->get(3)?->created_at],
        4 => [$status === ComplaintStatus::Closed ? ComplaintStatus::Closed->label() : ComplaintStatus::Resolved->label(), $reached->get(4)?->created_at],
    ];
@endphp

<ol {{ $attributes->class('grid gap-4 sm:grid-cols-4 sm:gap-0') }} aria-label="{{ __('Complaint progress') }}">
    @foreach ($steps as $n => [$label, $at])
        @php($done = $n <= $current)
        <li class="relative flex items-start gap-3 sm:flex-col sm:items-center sm:gap-2 sm:text-center" @if ($n === $current) aria-current="step" @endif>
            {{-- Connector: vertical on phones, horizontal from sm --}}
            @unless ($loop->last)
                <span @class([
                    'absolute start-[15px] top-8 h-[calc(100%-0.5rem)] w-0.5 sm:start-[calc(50%+1.25rem)] sm:top-[15px] sm:h-0.5 sm:w-[calc(100%-2.5rem)] rtl:sm:start-auto rtl:sm:end-[calc(50%+1.25rem)]',
                    'bg-brand-600' => $n < $current,
                    'bg-slate-200' => $n >= $current,
                ])></span>
            @endunless
            <span @class([
                'relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-semibold ltr-nums',
                'bg-brand-700 text-white' => $n < $current,
                'bg-brand-700 text-white ring-4 ring-brand-100' => $n === $current,
                'bg-slate-200 text-slate-500' => ! $done,
            ])>
                @if ($n < $current || ($n === $current && $status->isFinal()))
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                @else
                    {{ $n }}
                @endif
            </span>
            <span class="min-w-0 pb-1">
                <span @class(['block text-sm font-semibold', 'text-slate-900' => $done, 'text-slate-400' => ! $done])>{{ $label }}</span>
                @if ($done && $at)
                    <span class="block text-xs text-slate-500 ltr-nums">{{ $at->format('d M Y') }}</span>
                @endif
            </span>
        </li>
    @endforeach
</ol>
