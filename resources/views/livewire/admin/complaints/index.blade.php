@php($filtered = (bool) ($status || $court || $country || $from || $to))

<div>
    <x-admin.page-header title="All complaints" :description="number_format($complaints->total()).' '.Str::plural('complaint', $complaints->total()).($filtered || $search ? ' match your filters' : '')" />

    {{-- Filters --}}
    <div x-data="{ more: @js($filtered) }" class="mb-4 rounded-xl bg-white p-3 shadow-[var(--shadow-card)] ring-1 ring-slate-200">
        <div class="flex flex-wrap items-center gap-2">
            <x-ui.input name="search" type="search" wire:model.live.debounce.400ms="search" placeholder="Tracking no., case no., title, citizen or CNIC…" wrapper="flex-1 min-w-48" aria-label="Search" />
            <x-ui.button variant="secondary" size="sm" x-on:click="more = ! more" ::aria-expanded="more">
                Filters
                @if ($filtered) <span class="rounded-full bg-brand-600 px-1.5 text-xs text-white">on</span> @endif
            </x-ui.button>
            @if ($search || $filtered)
                <x-ui.button variant="ghost" size="sm" wire:click="clearFilters">Clear</x-ui.button>
            @endif
        </div>
        <div x-show="more" x-cloak x-transition class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <x-ui.select name="status" wire:model.live="status" label="Status" placeholder="Any status" :options="$statuses" />
            <x-ui.select name="court" wire:model.live="court" label="Court" placeholder="All courts" :options="$courts" wrapper="lg:col-span-2" />
            <x-ui.select name="country" wire:model.live="country" label="Country" placeholder="All countries" :options="$countries" />
            <div class="grid grid-cols-2 gap-3 sm:col-span-2 lg:col-span-5 lg:max-w-md">
                <x-ui.input name="from" type="date" wire:model.live="from" label="Filed from" />
                <x-ui.input name="to" type="date" wire:model.live="to" label="Filed to" />
            </div>
        </div>
    </div>

    {{-- List: cards on phones, table from md --}}
    <div class="relative">
        <div wire:loading.delay class="absolute inset-x-0 top-0 z-10 h-0.5 animate-pulse rounded-full bg-brand-500"></div>

        <ul class="space-y-3 md:hidden">
            @forelse ($complaints as $complaint)
                <li wire:key="m-{{ $complaint->id }}">
                    <a href="{{ route('admin.complaints.show', $complaint) }}" wire:navigate class="block rounded-xl border border-slate-200 bg-white p-4 shadow-[var(--shadow-card)]">
                        <div class="flex items-start justify-between gap-3">
                            <p class="font-mono text-sm font-semibold text-brand-800">{{ $complaint->tracking_number }}</p>
                            <x-ui.badge :color="$complaint->status->color()">{{ $complaint->status->label() }}</x-ui.badge>
                        </div>
                        <p class="mt-1 font-medium text-slate-900" dir="auto">{{ $complaint->case_title }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $complaint->court->name_en }}, {{ $complaint->court->district_en }} · {{ $complaint->case_number }}</p>
                        <p class="text-xs text-slate-500">{{ $complaint->citizen->name }} · {{ \App\Support\Countries::name($complaint->citizen->country, 'en') }} · {{ $complaint->created_at->format('d M Y') }}</p>
                    </a>
                </li>
            @empty
                <li class="rounded-xl border border-dashed border-slate-300 bg-white py-12 text-center text-sm text-slate-500">No complaints found.</li>
            @endforelse
        </ul>

        <x-ui.table class="hidden md:block">
            <x-slot:head>
                <th>Tracking no.</th>
                <th>Case</th>
                <th>Court</th>
                <th class="hidden lg:table-cell">Citizen</th>
                <th>Status</th>
                <th class="hidden xl:table-cell">Filed</th>
                <th class="w-12"><span class="sr-only">Actions</span></th>
            </x-slot:head>

            @forelse ($complaints as $complaint)
                @php($url = route('admin.complaints.show', $complaint))
                <tr wire:key="c-{{ $complaint->id }}" x-data x-on:click="if (! $event.target.closest('a,button')) Livewire.navigate(@js($url))" class="cursor-pointer">
                    <td class="whitespace-nowrap">
                        <a href="{{ $url }}" wire:navigate class="font-mono text-sm font-semibold text-brand-800 hover:underline">{{ $complaint->tracking_number }}</a>
                        @if ($complaint->submitted_via === \App\Models\Complaint::VIA_CHATBOT)
                            <x-ui.badge class="ms-1">Chatbot</x-ui.badge>
                        @endif
                    </td>
                    <td class="max-w-xs">
                        <p class="truncate font-medium text-slate-900" dir="auto" title="{{ $complaint->case_title }}">{{ $complaint->case_title }}</p>
                        <p class="text-xs text-slate-500">{{ $complaint->case_number }} · {{ $complaint->category->label() }}</p>
                    </td>
                    <td>
                        <p class="whitespace-nowrap">{{ $complaint->court->name_en }}</p>
                        <p class="text-xs text-slate-500">{{ $complaint->court->district_en }}</p>
                    </td>
                    <td class="hidden lg:table-cell">
                        <p class="whitespace-nowrap">{{ $complaint->citizen->name }}</p>
                        <p class="text-xs text-slate-500">{{ \App\Support\Countries::name($complaint->citizen->country, 'en') }}</p>
                    </td>
                    <td class="whitespace-nowrap"><x-ui.badge :color="$complaint->status->color()">{{ $complaint->status->label() }}</x-ui.badge></td>
                    <td class="hidden whitespace-nowrap text-slate-600 xl:table-cell">{{ $complaint->created_at->format('d M Y') }}</td>
                    <td class="text-end">
                        <x-ui.icon-button icon="eye" label="Open complaint" tone="brand" :href="$url" wire:navigate />
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-12 text-center text-slate-500">No complaints found.</td></tr>
            @endforelse
        </x-ui.table>
    </div>

    <div class="mt-4">{{ $complaints->links() }}</div>
</div>
