@php
    $limits = $this->limits();
    $citizen = auth('citizen')->user();
@endphp

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('File a Complaint') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('Tell us about the court case and the problem you faced. Fields marked * are required.') }}</p>
    </div>

    <form wire:submit="submit" class="space-y-6" novalidate>
        @if ($errors->any())
            <x-ui.alert type="error" :title="__('Please correct the highlighted fields.')">
                {{ $errors->first('submit') ?: __('Some information is missing or invalid.') }}
            </x-ui.alert>
        @endif

        {{-- Filed by --}}
        <x-ui.card :title="__('Filed by')" :description="__('The complaint is registered against your account.')">
            <dl class="grid gap-4 text-sm sm:grid-cols-3">
                <div>
                    <dt class="text-slate-500">{{ __('Full name') }}</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">{{ $citizen->name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Country of residence') }}</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">{{ $citizen->countryName() }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('CNIC / passport') }}</dt>
                    <dd class="mt-0.5 font-medium text-slate-900 ltr-nums">{{ $citizen->cnic_passport }}</dd>
                </div>
            </dl>
        </x-ui.card>

        {{-- Case --}}
        <x-ui.card :title="'1. '.__('Court case')" :description="__('Which case is your complaint about?')">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.field :label="__('Court / district')" for="court" name="form.court_id" required class="sm:col-span-2">
                    <select id="court" wire:model="form.court_id" required
                            @error('form.court_id') aria-invalid="true" aria-describedby="form.court_id-error" @enderror
                            @class([
                                'block w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm focus:outline-none focus:ring-2',
                                'border-slate-300 focus:border-brand-600 focus:ring-brand-600/20' => ! $errors->has('form.court_id'),
                                'border-red-400 focus:border-red-500 focus:ring-red-500/20' => $errors->has('form.court_id'),
                            ])>
                        <option value="">{{ __('Select court') }}</option>
                        @foreach ($this->courts as $district => $courts)
                            <optgroup label="{{ $district }}">
                                @foreach ($courts as $court)
                                    <option value="{{ $court->id }}">{{ $court->label }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </x-ui.field>

                <x-ui.input name="form.case_number" wire:model.blur="form.case_number" :label="__('Case number')" required maxlength="50"
                            placeholder="123/2025" class="ltr-nums" :hint="__('As written on the court file or summons.')" />

                <x-ui.select name="form.category" wire:model="form.category" :label="__('Complaint category')" required
                             :placeholder="__('Select category')" :options="$categories" />

                <x-ui.input name="form.case_title" wire:model.blur="form.case_title" :label="__('Case title')" required maxlength="200"
                            :placeholder="__('e.g. Ahmed Khan vs. Muhammad Aslam')" wrapper="sm:col-span-2" />
            </div>
        </x-ui.card>

        {{-- Details --}}
        <x-ui.card :title="'2. '.__('Complaint details')" :description="__('Explain what happened, when, and what you would like done.')">
            <div class="space-y-6">
                <x-ui.textarea name="form.description" wire:model.blur="form.description" :label="__('Description')" rows="7" maxlength="5000" required
                               :placeholder="__('Describe the problem you faced during the proceedings.')" />

                {{-- Attachments --}}
                <div>
                    <p class="text-sm font-medium text-slate-700">{{ __('Attachments (optional)') }}</p>
                    <div x-data="dropzone()"
                         @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="drop($event)"
                         :class="dragging ? 'border-brand-600 bg-brand-50' : 'border-slate-300 bg-white'"
                         class="mt-1.5 rounded-xl border-2 border-dashed p-6 text-center transition">
                        <input x-ref="input" type="file" multiple class="sr-only" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/*"
                               @change="handle($event.target.files)" aria-label="{{ __('Choose files') }}">
                        <svg class="mx-auto h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/></svg>
                        <p class="mt-2 text-sm text-slate-700">
                            {{ __('Drag files here or') }}
                            <button type="button" @click="pick()" class="font-semibold text-brand-700 underline underline-offset-2">{{ __('browse') }}</button>
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ __('PDF or images, up to :size MB each, maximum :count files.', ['size' => $limits['max_file_kb'] / 1024, 'count' => $limits['max_files']]) }}
                        </p>
                        <div x-show="uploading" x-cloak class="mx-auto mt-3 h-1.5 max-w-xs overflow-hidden rounded-full bg-slate-200">
                            <div class="h-full bg-brand-600 transition-all" :style="`width: ${progress}%`"></div>
                        </div>
                    </div>
                    @error('attachments') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    @error('pendingAttachments.*') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    @error('attachments.*') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

                    @if ($attachments)
                        <ul class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white">
                            @foreach ($attachments as $index => $file)
                                <li wire:key="att-{{ $index }}-{{ $file->getFilename() }}" class="flex items-center gap-3 px-3 py-2 text-sm">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded bg-slate-100 text-[10px] font-bold uppercase text-slate-600">
                                        {{ $file->getClientOriginalExtension() }}
                                    </span>
                                    <span class="min-w-0 flex-1 truncate" dir="auto">{{ $file->getClientOriginalName() }}</span>
                                    <span class="text-xs text-slate-500 ltr-nums">{{ Number::fileSize($file->getSize()) }}</span>
                                    <button type="button" wire:click="removeAttachment({{ $index }})" class="rounded p-1 text-slate-400 hover:bg-red-50 hover:text-red-600">
                                        <span class="sr-only">{{ __('Remove') }}</span>
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- Honeypot: hidden from people and screen readers --}}
                <div class="hidden" aria-hidden="true">
                    <label for="website">Website</label>
                    <input id="website" type="text" wire:model="form.website" tabindex="-1" autocomplete="off">
                </div>

                <div>
                    <label class="flex items-start gap-3 text-sm text-slate-700">
                        <input type="checkbox" wire:model="form.declaration" class="mt-1 h-4 w-4 rounded border-slate-300 text-brand-700 focus:ring-brand-600">
                        <span>{{ __('I confirm that the information I have given is true to the best of my knowledge.') }}</span>
                    </label>
                    @error('form.declaration') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <x-slot:footer>
                <div class="flex flex-col-reverse items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-slate-500">{{ __('You will receive a tracking number right after submitting.') }}</p>
                    <x-ui.button type="submit" size="lg" wire:loading.attr="disabled" wire:target="submit,pendingAttachments">
                        <svg wire:loading wire:target="submit" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                        <span wire:loading.remove wire:target="submit">{{ __('Submit complaint') }}</span>
                        <span wire:loading wire:target="submit">{{ __('Submitting…') }}</span>
                    </x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.card>
    </form>
</div>
