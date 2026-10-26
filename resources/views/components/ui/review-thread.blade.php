{{--
    Read-only thread of admin follow-ups (remarks and status changes), oldest first.
    <x-ui.review-thread :reviews="$complaint->reviews" />            citizen view
    <x-ui.review-thread :reviews="$complaint->reviews" staff />      admin view: shows who wrote it
--}}
@props(['reviews', 'staff' => false])

@if ($reviews->isEmpty())
    <p {{ $attributes->class('rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500') }}>
        {{ $staff ? 'No reviews yet. Add the first one below.' : __('No updates yet. The court administration will post remarks here as they review your complaint.') }}
    </p>
@else
    <ol {{ $attributes->class('space-y-4') }}>
        @foreach ($reviews as $review)
            <li wire:key="review-{{ $review->id }}" class="flex gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-700 text-white" aria-hidden="true">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z"/></svg>
                </span>
                <div class="min-w-0 flex-1 rounded-2xl rounded-ss-md border border-slate-200 bg-white px-4 py-3">
                    <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                        <p class="text-sm font-semibold text-slate-900">
                            {{ $staff ? ($review->user?->name ?? 'Admin') : __('Court administration') }}
                        </p>
                        <time class="text-xs text-slate-500 ltr-nums" datetime="{{ $review->created_at->toIso8601String() }}">{{ $review->created_at->format('d M Y, h:i A') }}</time>
                    </div>
                    @if ($review->changesStatus())
                        <p class="mt-1.5 flex flex-wrap items-center gap-1.5 text-xs text-slate-600">
                            {{ __('Status changed') }}
                            <x-ui.badge :color="$review->status_from?->color() ?? 'slate'">{{ $review->status_from?->label() }}</x-ui.badge>
                            <span aria-hidden="true" class="rtl:-scale-x-100">&rarr;</span>
                            <x-ui.badge :color="$review->status_to->color()">{{ $review->status_to->label() }}</x-ui.badge>
                        </p>
                    @endif
                    @if ($review->body)
                        <p class="mt-2 whitespace-pre-line break-words text-sm leading-relaxed text-slate-700" dir="auto">{{ $review->body }}</p>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
@endif
