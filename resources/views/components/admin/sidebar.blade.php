@php
    $icons = [
        'home' => 'M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75',
        'inbox' => 'M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z',
    ];

    // Complaints nobody has looked at yet.
    $new = \App\Models\Complaint::query()->withStatus(\App\Enums\ComplaintStatus::Submitted)->count();

    $sections = [
        null => [
            ['Dashboard', route('admin.dashboard'), 'home', 'admin.dashboard'],
            ['Complaints', route('admin.complaints.index'), 'inbox', 'admin.complaints.*', $new ?: null],
        ],
    ];
@endphp

@foreach ($sections as $heading => $items)
    <div @class(['pt-4' => $heading])>
        @if ($heading)
            <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wider text-brand-400">{{ $heading }}</p>
        @endif
        @foreach ($items as $item)
            @php([$label, $url, $icon, $pattern] = $item)
            <x-admin.nav-link :href="$url" :icon="$icons[$icon]" :active="request()->routeIs($pattern)" :badge="$item[4] ?? null">{{ $label }}</x-admin.nav-link>
        @endforeach
    </div>
@endforeach
