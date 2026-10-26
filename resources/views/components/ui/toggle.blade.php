{{-- Accessible switch bound to a boolean (pass wire:model). --}}
@props(['label', 'name' => null, 'hint' => null])

<label class="flex cursor-pointer items-start gap-3">
    <span class="relative mt-0.5 inline-flex shrink-0">
        <input type="checkbox" role="switch" {{ $attributes->class('peer sr-only') }} @if ($name) name="{{ $name }}" @endif>
        <span class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-brand-600 peer-focus-visible:ring-2 peer-focus-visible:ring-brand-600 peer-focus-visible:ring-offset-2"></span>
        <span class="absolute start-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5 rtl:peer-checked:-translate-x-5"></span>
    </span>
    <span class="text-sm">
        <span class="font-medium text-slate-800">{{ $label }}</span>
        @if ($hint)
            <span class="block text-xs text-slate-500">{{ $hint }}</span>
        @endif
    </span>
</label>
