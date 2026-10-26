<div class="bg-slate-50">
    <div class="bg-gradient-to-br from-brand-900 to-brand-700 text-white">
        <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6">
            <h1 class="text-2xl font-bold sm:text-3xl">{{ __('Track Complaint') }}</h1>
            <p class="mt-2 text-brand-100">{{ __('Enter your tracking number and the CNIC / passport number the complaint was filed with.') }}</p>
        </div>
    </div>

    <div class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6">
        <x-ui.card>
            <form wire:submit="track" class="grid gap-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end" novalidate>
                <x-ui.input name="trackingNumber" wire:model="trackingNumber" :label="__('Tracking number')" placeholder="OJC-2026-00001"
                            class="ltr-nums uppercase" autocomplete="off" required />
                <x-ui.input name="identity" wire:model="identity" :label="__('CNIC / passport number')" placeholder="12345-1234567-1"
                            class="ltr-nums uppercase" autocomplete="off" required />
                <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="track">{{ __('Track') }}</x-ui.button>
            </form>
        </x-ui.card>

        @if ($complaint)
            <x-ui.card>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Tracking number') }}</p>
                        <p class="mt-1 break-all font-mono text-xl font-bold text-slate-900 ltr-nums">{{ $complaint->tracking_number }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $complaint->court->label }} · <span class="ltr-nums">{{ $complaint->case_number }}</span></p>
                    </div>
                    <x-ui.badge :color="$complaint->status->color()" class="!px-3 !py-1 !text-sm">{{ $complaint->status->label() }}</x-ui.badge>
                </div>
                <x-ui.status-timeline :complaint="$complaint" class="mt-8" />
            </x-ui.card>

            <x-ui.card :title="__('Reviews & remarks')">
                <x-ui.review-thread :reviews="$complaint->reviews" />
                <x-slot:footer>
                    <p class="text-sm text-slate-600">
                        {{ __('Log in to see the full details and attachments.') }}
                        <a href="{{ route('citizen.login') }}" class="font-semibold text-brand-700 hover:underline">{{ __('Citizen login') }}</a>
                    </p>
                </x-slot:footer>
            </x-ui.card>
        @elseif ($searched)
            <x-ui.alert type="warning" :title="__('No complaint found')">
                {{ __('Please check the tracking number and the CNIC / passport number and try again.') }}
            </x-ui.alert>
        @endif
    </div>
</div>
