{{-- Wraps any control with a label, optional hint and the validation error for `name`. --}}
@props([
    'label' => null,
    'for' => null,
    'name' => null,
    'hint' => null,
    'required' => false,
])

<div {{ $attributes->class('space-y-1.5') }}>
    @if ($label)
        <label for="{{ $for ?? $name }}" class="block text-sm font-medium text-slate-700">
            {{ $label }}
            @if ($required)
                <span class="text-red-600" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if ($hint)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif

    @if ($name)
        @error($name)
            {{-- Same red as the "Rejected" status. --}}
            <p class="flex items-start gap-1.5 text-sm font-medium text-red-700" id="{{ $name }}-error">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                {{ $message }}
            </p>
        @enderror
    @endif
</div>
