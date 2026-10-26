@props([
    'name',
    'label' => null,
    'type' => 'text',
    'hint' => null,
    'required' => false,
    'wrapper' => null, // layout classes for the field wrapper, e.g. sm:col-span-2
])

@php($id = $attributes->get('id', $name))

<x-ui.field :label="$label" :for="$id" :name="$name" :hint="$hint" :required="$required" :class="$wrapper">
    <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}"
           @if ($required) required @endif
           @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
           {{ $attributes->except('id')->class([
               'block w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2',
               'border-slate-300 focus:border-brand-600 focus:ring-brand-600/20' => ! $errors->has($name),
               'border-red-400 focus:border-red-500 focus:ring-red-500/20' => $errors->has($name),
           ]) }}>
</x-ui.field>
