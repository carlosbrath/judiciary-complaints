@php
    use App\Enums\ComplaintCategory;
    use App\Enums\ComplaintStatus;
    use App\Models\Court;

    $districts = Court::query()->active()->ordered()->get(['district_en', 'district_ur'])
        ->map(fn (Court $c) => $c->district)->unique()->values();

    $steps = [
        ['title' => __('Create your account'), 'text' => __('Register from anywhere in the world with your CNIC/NICOP or passport number and WhatsApp number.'),
            'icon' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
        ['title' => __('Describe the problem'), 'text' => __('Choose the court, give the case number and title, pick a category and explain what happened. Attach documents if you have them.'),
            'icon' => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10'],
        ['title' => __('Reviewed by the judiciary'), 'text' => __('Your complaint gets a tracking number and is reviewed by the court administration, who post remarks as they act on it.'),
            'icon' => 'M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z'],
        ['title' => __('Follow it to resolution'), 'text' => __('See every status change and remark on your dashboard — Submitted, Under Review, In Progress, Resolved.'),
            'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];
    $trust = [__('Free to file'), __('Tracking number on submission'), __('English & Urdu')];
    $check = 'M4.5 12.75l6 6 9-13.5';
    $timeline = [ComplaintStatus::Submitted, ComplaintStatus::UnderReview, ComplaintStatus::InProgress, ComplaintStatus::Resolved];
@endphp

<x-layouts.public>
    {{-- ================================================================ Hero --}}
    <section class="on-dark relative isolate overflow-hidden bg-brand-800 text-white" aria-labelledby="hero-title">
        {{-- Four flowing lines: the flag's four white stripes / Kashmir's four rivers --}}
        <svg class="pointer-events-none absolute inset-0 -z-10 h-full w-full" viewBox="0 0 1440 880" preserveAspectRatio="none" fill="none" aria-hidden="true">
            <path d="M-40 250 C 260 170, 470 340, 760 250 S 1250 140, 1500 230" stroke="#fff" stroke-opacity=".07" stroke-width="3"/>
            <path d="M-40 350 C 300 270, 520 440, 820 350 S 1260 250, 1500 330" stroke="#fff" stroke-opacity=".06" stroke-width="3"/>
            <path d="M-40 450 C 240 380, 560 540, 860 450 S 1280 350, 1500 430" stroke="#fff" stroke-opacity=".05" stroke-width="3"/>
            <path d="M-40 550 C 280 480, 600 640, 900 550 S 1300 450, 1500 530" stroke="#fff" stroke-opacity=".04" stroke-width="3"/>
        </svg>
        {{-- Mountain ridgeline --}}
        <svg class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-32 w-full sm:h-44" viewBox="0 0 1440 180" preserveAspectRatio="none" aria-hidden="true">
            <path fill="var(--green-900)" d="M0 110 L90 70 L170 98 L270 38 L360 88 L450 60 L560 104 L660 30 L760 84 L860 56 L960 96 L1070 22 L1170 80 L1270 52 L1360 90 L1440 64 V180 H0Z"/>
            <path fill="var(--green-950)" d="M0 150 L120 112 L220 140 L330 96 L440 138 L560 108 L690 146 L800 104 L920 140 L1040 110 L1160 148 L1280 116 L1380 142 L1440 124 V180 H0Z"/>
        </svg>

        <div class="mx-auto grid max-w-7xl gap-x-10 gap-y-10 px-4 pb-40 pt-12 sm:px-6 sm:pb-48 md:pt-16 lg:grid-cols-12 lg:px-8 xl:pt-20">
            {{-- Copy --}}
            <div class="order-1 lg:col-span-6 lg:row-span-2 lg:self-center">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-1.5 text-xs font-semibold text-white ring-1 ring-white/20 sm:text-sm">
                    <span class="h-2 w-2 rounded-full bg-accent-500"></span>
                    {{ __('Lower Judiciary · Azad Jammu & Kashmir') }}
                </p>

                <h1 id="hero-title" class="mt-6 text-4xl font-extrabold leading-[1.05] sm:text-5xl xl:text-6xl">
                    {!! __('Living abroad? Your case still :matters.', ['matters' => '<span class="text-accent-500">'.e(__('matters')).'</span>']) !!}
                </h1>

                <p lang="ur" dir="rtl" class="mt-5 text-lg text-brand-50 sm:text-xl">بیرونِ ملک مقیم شہریوں کے لیے — عدالتی مقدمات سے متعلق شکایت آن لائن درج کریں</p>

                <p class="mt-4 max-w-xl text-base leading-relaxed text-brand-100 sm:text-lg">
                    {{ __('Overseas citizens can report delays, missing records or any problem they faced in a court case of the lower judiciary of AJ&K — and follow it online until it is resolved.') }}
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <x-ui.button :href="route('citizen.complaints.create')" variant="accent" size="lg" class="!rounded-full !px-7 text-brand-950">
                        {{ __('File a Complaint') }} <span aria-hidden="true" class="rtl:-scale-x-100">&rarr;</span>
                    </x-ui.button>
                    <a href="{{ route('complaints.track') }}"
                       class="inline-flex min-h-12 items-center justify-center rounded-full px-7 text-base font-semibold text-white ring-2 ring-inset ring-white/80 transition hover:bg-white hover:text-brand-900">
                        {{ __('Track a Complaint') }}
                    </a>
                </div>

                <ul class="mt-6 flex flex-wrap gap-x-6 gap-y-2 text-sm font-medium text-brand-50">
                    @foreach ($trust as $item)
                        <li class="flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-accent-500 text-brand-950">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $check }}"/></svg>
                            </span>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Tracking card: straight under the buttons on phones, overlapping the illustration on desktop --}}
            <div class="relative z-10 order-2 lg:order-3 lg:col-span-5 lg:col-start-8 lg:row-start-2 lg:-mt-10 lg:ms-16 xl:ms-24">
                <div class="rounded-2xl bg-white p-6 text-slate-800 shadow-[0_24px_60px_-16px_rgb(2_20_12/0.55)] ring-1 ring-black/5 sm:p-7">
                    <div class="flex items-start gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-100 text-brand-800">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                        </span>
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900">{{ __('Track Complaint') }}</h2>
                            <p class="text-sm text-slate-600">{{ __('Enter your tracking number and CNIC / passport number.') }}</p>
                        </div>
                    </div>
                    <form action="{{ route('complaints.track') }}" method="get" class="mt-5 space-y-3">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <x-ui.input name="ref" :label="__('Tracking number')" placeholder="OJC-2026-00001"
                                        class="ltr-nums min-h-11 uppercase" autocomplete="off" required />
                            <x-ui.input name="id" :label="__('CNIC / passport')" placeholder="12345-1234567-1"
                                        class="ltr-nums min-h-11 uppercase" autocomplete="off" required />
                        </div>
                        <x-ui.button type="submit" class="w-full !rounded-xl">{{ __('Track') }}</x-ui.button>
                    </form>
                </div>
            </div>

            {{-- Flag + case-status illustration --}}
            <div class="relative order-3 mx-auto w-full max-w-md sm:max-w-lg lg:order-2 lg:col-span-6 lg:col-start-7 lg:row-start-1 lg:max-w-none" aria-hidden="true">
                <div class="relative aspect-[5/4]">
                    <div class="absolute end-0 top-4 w-[82%] md:-rotate-[4deg]">
                        <x-public.flag fabric class="w-full rounded-md shadow-[0_30px_60px_-20px_rgb(0_0_0/0.6)]" />
                    </div>
                    <div class="absolute bottom-0 start-0 w-[62%] rounded-2xl bg-white p-4 text-slate-800 shadow-[0_30px_60px_-20px_rgb(0_0_0/0.65)] ring-4 ring-white sm:p-5">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Tracking number') }}</p>
                        <p class="font-mono text-sm font-bold text-brand-900 ltr-nums sm:text-base">OJC-2026-00042</p>
                        <ol class="mt-3 space-y-2">
                            @foreach ($timeline as $status)
                                <li class="flex items-center gap-2 text-xs sm:text-sm">
                                    <span @class(['flex h-5 w-5 shrink-0 items-center justify-center rounded-full', 'bg-brand-600 text-white' => ! $loop->last, 'bg-slate-200' => $loop->last])>
                                        @unless ($loop->last)
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $check }}"/></svg>
                                        @endunless
                                    </span>
                                    <span @class(['font-medium', 'text-slate-900' => ! $loop->last, 'text-slate-400' => $loop->last])>{{ $status->label() }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================================================================ How it works --}}
    <section id="how-it-works" class="scroll-mt-24 bg-ivory py-20 sm:py-24" aria-labelledby="how-title">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="text-sm font-bold uppercase tracking-[0.14em] text-accent-800">{{ __('How it works') }}</p>
            <h2 id="how-title" class="mt-3 max-w-3xl text-3xl font-extrabold text-brand-950 sm:text-4xl">{{ __('From your complaint to a resolution — in four clear steps.') }}</h2>

            <ol class="mt-12 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($steps as $step)
                    @php($last = $loop->last)
                    <li @class([
                        'relative flex flex-col rounded-2xl p-7 transition',
                        'bg-white text-slate-700 shadow-[var(--shadow-card)] ring-1 ring-slate-900/5 hover:-translate-y-1 hover:shadow-lg' => ! $last,
                        'on-dark bg-brand-800 text-brand-50 shadow-xl' => $last,
                    ])>
                        <div class="flex items-center justify-between">
                            <span @class(['flex h-12 w-12 items-center justify-center rounded-xl', 'bg-brand-100 text-brand-800' => ! $last, 'bg-accent-500 text-brand-950' => $last])>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $step['icon'] }}"/></svg>
                            </span>
                            <span @class(['font-display text-4xl font-extrabold ltr-nums', 'text-slate-200' => ! $last, 'text-white/25' => $last])>{{ sprintf('%02d', $loop->iteration) }}</span>
                        </div>
                        <h3 @class(['mt-6 text-xl font-bold', 'text-brand-950' => ! $last, 'text-white' => $last])>{{ $step['title'] }}</h3>
                        <p class="mt-2 text-[15px] leading-relaxed">{{ $step['text'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ================================================================ Categories --}}
    <section class="bg-white py-20 sm:py-24" aria-labelledby="categories-title">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.14em] text-accent-800">{{ __('What you can report') }}</p>
                    <h2 id="categories-title" class="mt-3 text-3xl font-extrabold text-brand-950 sm:text-4xl">{{ __('Any problem you faced during court proceedings.') }}</h2>
                </div>
                <a href="{{ route('citizen.complaints.create') }}" class="inline-flex min-h-11 items-center gap-1.5 rounded-lg font-semibold text-brand-700 hover:text-brand-900 hover:underline">
                    {{ __('File a Complaint') }} <span aria-hidden="true" class="rtl:-scale-x-100">&rarr;</span>
                </a>
            </div>

            <ul class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (ComplaintCategory::cases() as $category)
                    <li class="flex h-full items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-brand-500 hover:shadow-lg">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 font-display text-lg font-extrabold text-brand-700 ring-1 ring-brand-100 ltr-nums">{{ $loop->iteration }}</span>
                        <span class="font-bold text-slate-900">{{ $category->label() }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- ================================================================ Districts --}}
    <section class="on-dark relative overflow-hidden bg-brand-900 py-16 text-white sm:py-20" aria-labelledby="districts-title">
        <svg class="pointer-events-none absolute -end-20 top-0 h-full w-2/3 opacity-[0.06]" viewBox="0 0 400 200" fill="none" aria-hidden="true">
            @foreach ([40, 80, 120, 160] as $y)
                <path d="M0 {{ $y }} C 100 {{ $y - 30 }}, 200 {{ $y + 30 }}, 400 {{ $y }}" stroke="#fff" stroke-width="6"/>
            @endforeach
        </svg>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 id="districts-title" class="max-w-3xl text-2xl font-extrabold sm:text-3xl">{{ __('Courts in every district of AJ&K — from Neelum to Bhimber.') }}</h2>
            <ul class="mt-8 flex flex-wrap gap-2.5">
                @foreach ($districts as $district)
                    <li class="rounded-full px-4 py-2 text-sm font-semibold text-white ring-1 ring-inset ring-white/40">{{ $district }}</li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- ================================================================ Account CTA --}}
    <section class="bg-ivory py-20 sm:py-24" aria-labelledby="cta-title">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl bg-accent-500 px-6 py-10 sm:px-12 sm:py-12">
                <svg class="pointer-events-none absolute -end-10 -top-10 h-56 w-56 text-white/20" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>
                <div class="relative flex flex-col items-start justify-between gap-6 lg:flex-row lg:items-center">
                    <div class="max-w-2xl">
                        <h2 id="cta-title" class="text-2xl font-extrabold text-brand-950 sm:text-3xl">{{ __('No need to travel home to raise a court problem.') }}</h2>
                        <p class="mt-2 text-brand-950/80">{{ __('Create a free account, file your complaint in minutes and follow every update online — in English or Urdu.') }}</p>
                    </div>
                    <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                        <a href="{{ route('citizen.register') }}"
                           class="inline-flex min-h-12 items-center justify-center rounded-full bg-brand-900 px-7 font-semibold text-white shadow-sm transition hover:bg-brand-950">{{ __('Create an account') }}</a>
                        <a href="{{ route('citizen.login') }}"
                           class="inline-flex min-h-12 items-center justify-center rounded-full px-7 font-semibold text-brand-950 ring-2 ring-inset ring-brand-950 transition hover:bg-brand-950 hover:text-white">{{ __('Log in') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
