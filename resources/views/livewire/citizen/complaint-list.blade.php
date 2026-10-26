<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('My complaints') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('All complaints registered against your account.') }}</p>
        </div>
        <x-ui.button :href="route('citizen.complaints.create')">{{ __('File a Complaint') }}</x-ui.button>
    </div>

    <dl class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach ($stats as [$label, $value])
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-[var(--shadow-card)]">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</dt>
                <dd class="mt-1 font-display text-2xl font-extrabold text-brand-900 ltr-nums">{{ $value }}</dd>
            </div>
        @endforeach
    </dl>

    @if ($complaints->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
            <p class="text-slate-500">{{ __('You have not filed any complaints yet.') }}</p>
            <x-ui.button :href="route('citizen.complaints.create')" variant="secondary" class="mt-4">{{ __('File your first complaint') }}</x-ui.button>
        </div>
    @else
        <ul class="space-y-3">
            @foreach ($complaints as $complaint)
                <li wire:key="c-{{ $complaint->id }}">
                    <a href="{{ route('citizen.complaints.show', $complaint) }}"
                       class="block rounded-xl border border-slate-200 bg-white p-4 shadow-[var(--shadow-card)] transition hover:border-brand-300 sm:p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-mono text-sm font-semibold text-brand-800 ltr-nums">{{ $complaint->tracking_number }}</p>
                                <p class="mt-1 font-medium text-slate-900" dir="auto">{{ $complaint->case_title }}</p>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $complaint->court->label }} · <span class="ltr-nums">{{ $complaint->case_number }}</span>
                                    · <span class="ltr-nums">{{ $complaint->created_at->format('d M Y') }}</span>
                                </p>
                            </div>
                            <x-ui.badge :color="$complaint->status->color()">{{ $complaint->status->label() }}</x-ui.badge>
                        </div>
                        @if ($complaint->latestReview)
                            <p class="mt-3 line-clamp-2 border-t border-slate-100 pt-3 text-sm text-slate-600" dir="auto">
                                <span class="font-semibold text-slate-700">{{ __('Latest remark:') }}</span> {{ $complaint->latestReview->body }}
                            </p>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="mt-6">{{ $complaints->links() }}</div>
    @endif
</div>
