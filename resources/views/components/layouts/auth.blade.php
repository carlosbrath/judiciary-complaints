{{-- Admin sign-in page. English only, like the rest of the admin panel. --}}
@props(['title' => null])

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    @include('partials.head', ['title' => $title])
</head>
<body class="min-h-screen bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800">
    <main class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <a href="{{ route('home') }}" class="mb-6 flex items-center gap-3 text-white">
            <img src="{{ asset('images/logo.png') }}" alt="" class="h-12 w-12 rounded-full bg-white p-1">
            <span class="leading-tight">
                <span class="block font-bold">Overseas Judiciary Complaint Portal</span>
                <span class="block text-sm text-brand-200">Admin Panel · Lower Judiciary of AJ&amp;K</span>
            </span>
        </a>

        {{ $slot }}
    </main>
</body>
</html>
