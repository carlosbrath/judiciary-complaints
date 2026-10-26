{{--
    Row-action button with an icon, a tooltip and a screen-reader label.
    <x-ui.icon-button icon="edit" label="Edit" wire:click="edit(1)" />
    Pass href="…" to render a link instead of a button.
--}}
@props(['icon', 'label', 'tone' => 'default', 'href' => null, 'type' => 'button'])

@php
    $paths = [
        'edit' => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125',
        'trash' => 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0',
        'power' => 'M5.636 5.636a9 9 0 1012.728 0M12 3v9',
        'key' => 'M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z',
        'mail' => 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
        'resend' => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99',
        'login-as' => 'M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9',
        'plus' => 'M12 4.5v15m7.5-7.5h-15',
        'up' => 'M4.5 15.75l7.5-7.5 7.5 7.5',
        'down' => 'M19.5 8.25l-7.5 7.5-7.5-7.5',
        'eye' => 'M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        'shield' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
    ];

    $tones = [
        'default' => 'text-slate-500 hover:bg-slate-100 hover:text-slate-800',
        'brand' => 'text-brand-700 hover:bg-brand-50 hover:text-brand-800',
        'danger' => 'text-red-600 hover:bg-red-50 hover:text-red-700',
        'success' => 'text-brand-600 hover:bg-brand-50',
    ];

    $classes = 'inline-flex h-9 w-9 items-center justify-center rounded-lg transition focus-visible:outline-2 focus-visible:outline-offset-1 '.($tones[$tone] ?? $tones['default']);
@endphp

@if ($href)
    <a href="{{ $href }}" title="{{ $label }}" {{ $attributes->class($classes) }}>
@else
    <button type="{{ $type }}" title="{{ $label }}" {{ $attributes->class($classes) }}>
@endif
    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $paths[$icon] ?? $paths['edit'] }}" />
    </svg>
    <span class="sr-only">{{ $label }}</span>
@if ($href)
    </a>
@else
    </button>
@endif
