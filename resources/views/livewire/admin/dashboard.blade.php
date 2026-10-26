@php
    $statusMax = max(1, $byStatus->max('count'));
    $courtMax = max(1, $byCourt->max('complaints_count') ?? 1);
@endphp

<div class="space-y-6">
    <x-admin.page-header title="Overview" description="Complaints from overseas citizens about the lower judiciary of AJ&K.">
        <x-slot:actions>
            <x-ui.button :href="route('admin.complaints.index')" wire:navigate>Open complaints</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ($cards as [$label, $count, $filter, $hint])
            <a href="{{ route('admin.complaints.index', array_filter(['status' => $filter])) }}" wire:navigate
               @class(['group rounded-[var(--radius-card)] border bg-white p-4 shadow-[var(--shadow-card)] transition hover:border-brand-300 sm:p-5',
                       'border-amber-200' => $filter === 'pending' && $count > 0,
                       'border-slate-200' => ! ($filter === 'pending' && $count > 0)])>
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p @class(['mt-1 text-3xl font-bold tabular-nums',
                           'text-amber-700' => $filter === 'pending' && $count > 0,
                           'text-slate-900' => ! ($filter === 'pending' && $count > 0)])>{{ number_format($count) }}</p>
                <p class="mt-0.5 text-xs text-slate-500">{{ $hint }}</p>
            </a>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="By status">
            <ul class="space-y-3">
                @foreach ($byStatus as $row)
                    <li>
                        <div class="flex items-center justify-between text-sm">
                            <a href="{{ route('admin.complaints.index', ['status' => $row['status']->value]) }}" wire:navigate class="font-medium text-slate-700 hover:text-brand-700">{{ $row['status']->label() }}</a>
                            <span class="tabular-nums text-slate-900">{{ number_format($row['count']) }}</span>
                        </div>
                        <div class="mt-1 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-brand-600" style="width: {{ round($row['count'] / $statusMax * 100) }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>

        <x-ui.card title="Courts with most complaints">
            @if ($byCourt->isEmpty())
                <p class="text-sm text-slate-500">No complaints yet.</p>
            @else
                <ul class="space-y-3">
                    @foreach ($byCourt as $court)
                        <li>
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <a href="{{ route('admin.complaints.index', ['court' => $court->id]) }}" wire:navigate class="min-w-0 truncate font-medium text-slate-700 hover:text-brand-700">{{ $court->name_en }}, {{ $court->district_en }}</a>
                                <span class="tabular-nums text-slate-900">{{ number_format($court->complaints_count) }}</span>
                            </div>
                            <div class="mt-1 h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-accent-500" style="width: {{ round($court->complaints_count / $courtMax * 100) }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    </div>

    <x-ui.card title="Latest complaints" :padding="false">
        <x-slot:actions>
            <a href="{{ route('admin.complaints.index') }}" wire:navigate class="text-sm font-semibold text-brand-700 hover:underline">View all</a>
        </x-slot:actions>
        @if ($recent->isEmpty())
            <p class="px-5 py-8 text-center text-sm text-slate-500 sm:px-6">No complaints yet.</p>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach ($recent as $complaint)
                    <li wire:key="recent-{{ $complaint->id }}">
                        <a href="{{ route('admin.complaints.show', $complaint) }}" wire:navigate class="flex flex-wrap items-center justify-between gap-3 px-5 py-3 hover:bg-slate-50 sm:px-6">
                            <div class="min-w-0">
                                <p class="font-mono text-sm font-semibold text-brand-800">{{ $complaint->tracking_number }}</p>
                                <p class="truncate text-sm text-slate-700" dir="auto">{{ $complaint->case_title }}</p>
                                <p class="text-xs text-slate-500">{{ $complaint->citizen->name }} · {{ \App\Support\Countries::name($complaint->citizen->country, 'en') }} · {{ $complaint->created_at->diffForHumans() }}</p>
                            </div>
                            <x-ui.badge :color="$complaint->status->color()">{{ $complaint->status->label() }}</x-ui.badge>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-ui.card>
</div>
