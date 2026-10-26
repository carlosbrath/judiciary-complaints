@php($citizen = $complaint->citizen)

<div class="space-y-6">
    <a href="{{ route('admin.complaints.index') }}" wire:navigate class="text-sm font-medium text-brand-700 hover:underline">&larr; All complaints</a>

    <x-ui.card>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Tracking number</p>
                <p class="mt-1 break-all font-mono text-xl font-bold text-slate-900">{{ $complaint->tracking_number }}</p>
                <p class="mt-1 text-sm text-slate-500">
                    Filed {{ $complaint->created_at->format('d M Y, h:i A') }} via {{ $complaint->submitted_via === \App\Models\Complaint::VIA_CHATBOT ? 'chatbot' : 'online form' }}
                </p>
            </div>
            <x-ui.badge :color="$complaint->status->color()" class="!px-3 !py-1 !text-sm">{{ $complaint->status->label() }}</x-ui.badge>
        </div>
        <x-ui.status-timeline :complaint="$complaint" class="mt-8" />
    </x-ui.card>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Complaint">
                <dl class="grid gap-4 text-sm sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <dt class="text-slate-500">Case title</dt>
                        <dd class="mt-0.5 font-medium text-slate-900" dir="auto">{{ $complaint->case_title }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Case number</dt>
                        <dd class="mt-0.5 font-medium text-slate-900">{{ $complaint->case_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Court</dt>
                        <dd class="mt-0.5 font-medium text-slate-900">{{ $complaint->court->name_en }}, {{ $complaint->court->district_en }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Category</dt>
                        <dd class="mt-0.5 font-medium text-slate-900">{{ $complaint->category->label() }}</dd>
                    </div>
                    @if ($complaint->resolved_at)
                        <div>
                            <dt class="text-slate-500">Resolved / closed on</dt>
                            <dd class="mt-0.5 font-medium text-slate-900">{{ $complaint->resolved_at->format('d M Y') }}</dd>
                        </div>
                    @endif
                    <div class="sm:col-span-2">
                        <dt class="text-slate-500">Description</dt>
                        <dd class="mt-1 whitespace-pre-line break-words rounded-lg bg-slate-50 p-3 text-slate-800" dir="auto">{{ $complaint->description }}</dd>
                    </div>
                </dl>

                @if ($complaint->attachments->isNotEmpty())
                    <div class="mt-6 border-t border-slate-100 pt-5">
                        <p class="text-sm font-medium text-slate-700">Attachments</p>
                        <ul class="mt-2 space-y-2">
                            @foreach ($complaint->attachments as $attachment)
                                <li class="flex flex-wrap items-center gap-3 text-sm">
                                    <a href="{{ route('admin.attachments.show', $attachment) }}" target="_blank" rel="noopener" class="font-medium text-brand-700 hover:underline" dir="auto">{{ $attachment->original_name }}</a>
                                    <span class="text-xs text-slate-500">{{ $attachment->size ? Number::fileSize($attachment->size) : '' }}</span>
                                    <a href="{{ route('admin.attachments.show', [$attachment, 'download' => 1]) }}" class="text-xs font-medium text-slate-500 hover:text-brand-700">Download</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card title="Reviews" description="Remarks and status changes. The citizen sees everything here.">
                <x-ui.review-thread :reviews="$complaint->reviews" staff />

                <form wire:submit="saveReview" class="mt-6 space-y-4 border-t border-slate-100 pt-5">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <x-ui.select name="status" wire:model="status" label="Status" :options="$statuses" required />
                        <x-ui.textarea name="remark" wire:model="remark" label="Follow-up review" rows="4" maxlength="5000" wrapper="sm:col-span-3"
                                       placeholder="Write a remark for the citizen, e.g. what action has been taken…" />
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-xs text-slate-500">Change the status, write a remark, or both.</p>
                        <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="saveReview">Save review</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <x-ui.card title="Citizen" class="self-start">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">Name</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">{{ $citizen->name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Country of residence</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">{{ \App\Support\Countries::name($citizen->country, 'en') }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">WhatsApp</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">
                        <a href="https://wa.me/{{ ltrim($citizen->phone, '+') }}" target="_blank" rel="noopener" class="hover:text-brand-700 hover:underline">{{ $citizen->phone }}</a>
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">CNIC / passport</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">{{ $citizen->cnic_passport }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Email</dt>
                    <dd class="mt-0.5 break-all font-medium text-slate-900">{{ $citizen->email ?? 'Not registered yet (filed via chatbot)' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Complaints filed</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">
                        <a href="{{ route('admin.complaints.index', ['search' => $citizen->cnic_passport]) }}" wire:navigate class="hover:text-brand-700 hover:underline">{{ $citizen->complaints()->count() }}</a>
                    </dd>
                </div>
            </dl>
        </x-ui.card>
    </div>
</div>
