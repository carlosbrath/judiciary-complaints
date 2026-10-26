@props(['title' => null])

@php($rtl = config('app.supported_locales.'.app()->getLocale().'.rtl', false))

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    @include('partials.head', ['title' => $title])
</head>
<body class="flex min-h-screen flex-col">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:shadow">
        {{ __('Skip to content') }}
    </a>

    <x-public.header />

    <main id="main" class="flex-1">
        {{ $slot }}
    </main>

    <x-public.footer />

    @unless (request()->routeIs('chatbot'))
        <x-public.chat-launcher />
    @endunless
</body>
</html>
