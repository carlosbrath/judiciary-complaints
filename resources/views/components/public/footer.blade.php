@php
    $contact = \App\Support\Site::all();
    $citizenLinks = [
        [__('File a Complaint'), route('citizen.complaints.create')],
        [__('Track Complaint'), route('complaints.track')],
        [__('How it works'), route('home').'#how-it-works'],
        [__('FAQs'), route('home').'#faqs'],
    ];
    $portalLinks = [
        [__('Create an account'), route('citizen.register')],
        [__('Citizen login'), route('citizen.login')],
        [__('Admin login'), route('admin.login')],
    ];
@endphp

<footer class="on-dark bg-brand-950 text-brand-100">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
        <div>
            <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-lg">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-700 ring-1 ring-white/10">
                    <x-public.flag class="h-5 w-auto rounded-[2px]" />
                </span>
                <span class="font-display text-lg font-extrabold leading-tight text-white">{{ __('Overseas Judiciary Complaint Portal') }}</span>
            </a>
            <p class="mt-4 text-sm leading-relaxed text-brand-200">
                {{ __('A direct channel for overseas citizens of Azad Jammu & Kashmir to raise problems with court cases in the lower judiciary — and follow every step until it is resolved.') }}
            </p>
        </div>

        <nav aria-labelledby="footer-citizens">
            <h2 id="footer-citizens" class="text-sm font-bold uppercase tracking-wider text-white">{{ __('For citizens') }}</h2>
            <ul class="mt-4 space-y-2.5 text-sm">
                @foreach ($citizenLinks as [$label, $href])
                    <li><a href="{{ $href }}" class="rounded hover:text-white hover:underline">{{ $label }}</a></li>
                @endforeach
            </ul>
        </nav>

        <nav aria-labelledby="footer-portal">
            <h2 id="footer-portal" class="text-sm font-bold uppercase tracking-wider text-white">{{ __('Portal') }}</h2>
            <ul class="mt-4 space-y-2.5 text-sm">
                @foreach ($portalLinks as [$label, $href])
                    <li><a href="{{ $href }}" class="rounded hover:text-white hover:underline">{{ $label }}</a></li>
                @endforeach
            </ul>
        </nav>

        <div>
            <h2 class="text-sm font-bold uppercase tracking-wider text-white">{{ __('Contact') }}</h2>
            <address class="mt-4 space-y-2.5 text-sm not-italic">
                <p>{{ __($contact['address']) }}</p>
                <p>{{ __('Helpline:') }} <a href="{{ \App\Support\Site::tel($contact['helpline']) }}" class="font-semibold text-white ltr-nums hover:underline">{{ $contact['helpline'] }}</a></p>
                <p>{{ __('WhatsApp:') }} <span class="font-semibold text-white ltr-nums">{{ $contact['whatsapp'] }}</span></p>
                <p>{{ __('Email:') }} <a href="mailto:{{ $contact['email'] }}" class="break-all font-semibold text-white hover:underline">{{ $contact['email'] }}</a></p>
            </address>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-3 px-4 py-5 text-xs text-brand-200 sm:flex-row sm:items-center sm:px-6 lg:px-8">
            <p>&copy; {{ now()->year }} {{ __('Lower Judiciary, Azad Jammu & Kashmir') }}</p>
            <div class="flex items-center gap-4">
                <p>{{ __('Developed by') }} <span class="font-semibold text-accent-300">AJK ITB</span></p>
                <span class="flex h-1.5 w-24 overflow-hidden rounded-full" aria-hidden="true">
                    <span class="flex-1 bg-accent-500"></span><span class="flex-1 bg-white"></span><span class="flex-1 bg-brand-500"></span>
                </span>
            </div>
        </div>
    </div>
</footer>
