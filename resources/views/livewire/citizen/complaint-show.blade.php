<div class="space-y-6">
    <a href="{{ route('citizen.complaints.index') }}" class="text-sm font-medium text-brand-700 hover:underline">
        <span aria-hidden="true" class="inline-block rtl:-scale-x-100">&larr;</span> {{ __('My complaints') }}
    </a>

    <x-ui.card>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0" x-data="copyText(@js($complaint->tracking_number))">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Tracking number') }}</p>
                <p class="mt-1 flex flex-wrap items-center gap-2">
                    <span class="break-all font-mono text-xl font-bold text-slate-900 ltr-nums">{{ $complaint->tracking_number }}</span>
                    <button type="button" @click="copy()" class="rounded-lg px-2 py-1 text-xs font-semibold text-brand-700 hover:bg-brand-50"
                            x-text="copied ? @js(__('Copied')) : @js(__('Copy'))">{{ __('Copy') }}</button>
                </p>
            </div>
            <x-ui.badge :color="$complaint->status->color()" class="!px-3 !py-1 !text-sm">{{ $complaint->status->label() }}</x-ui.badge>
        </div>

        <x-ui.status-timeline :complaint="$complaint" class="mt-8" />
    </x-ui.card>

    <div class="grid gap-6 lg:grid-cols-5">
        <x-ui.card :title="__('Complaint details')" class="lg:col-span-3">
            <dl class="grid gap-4 text-sm sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <dt class="text-slate-500">{{ __('Case title') }}</dt>
                    <dd class="mt-0.5 font-medium text-slate-900" dir="auto">{{ $complaint->case_title }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Case number') }}</dt>
                    <dd class="mt-0.5 font-medium text-slate-900 ltr-nums">{{ $complaint->case_number }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Court / district') }}</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">{{ $complaint->court->label }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Complaint category') }}</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">{{ $complaint->category->label() }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Submitted on') }}</dt>
                    <dd class="mt-0.5 font-medium text-slate-900 ltr-nums">{{ $complaint->created_at->format('d M Y, h:i A') }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-slate-500">{{ __('Description') }}</dt>
                    <dd class="mt-1 whitespace-pre-line break-words rounded-lg bg-slate-50 p-3 text-slate-800" dir="auto">{{ $complaint->description }}</dd>
                </div>
            </dl>

            @if ($complaint->attachments->isNotEmpty())
                <div class="mt-6 border-t border-slate-100 pt-5">
                    <p class="text-sm font-medium text-slate-700">{{ __('Attachments') }}</p>
                    <ul class="mt-2 space-y-2">
                        @foreach ($complaint->attachments as $attachment)
                            <li class="flex flex-wrap items-center gap-3 text-sm">
                                <a href="{{ route('citizen.attachments.show', $attachment) }}" target="_blank" rel="noopener" class="font-medium text-brand-700 hover:underline" dir="auto">
                                    {{ $attachment->original_name }}
                                </a>
                                <span class="text-xs text-slate-500 ltr-nums">{{ $attachment->size ? Number::fileSize($attachment->size) : '' }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </x-ui.card>

        <x-ui.card :title="__('Reviews & remarks')" :description="__('Updates from the court administration.')" class="lg:col-span-2">
            <x-ui.review-thread :reviews="$complaint->reviews" />
        </x-ui.card>
    </div>
</div>
