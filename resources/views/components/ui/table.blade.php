{{-- Usage: <x-ui.table><x-slot:head><th>…</th></x-slot:head> <tr>…</tr> </x-ui.table> --}}
<div {{ $attributes->class('overflow-x-auto rounded-[var(--radius-card)] border border-slate-200 bg-white shadow-[var(--shadow-card)]') }}>
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        @isset($head)
            <thead class="bg-slate-50 text-start text-xs font-semibold uppercase tracking-wide text-slate-500 [&_th]:px-4 [&_th]:py-3 [&_th:not(.text-end)]:text-start [&_th.text-end]:text-end">
                <tr>{{ $head }}</tr>
            </thead>
        @endisset
        <tbody class="divide-y divide-slate-100 [&_td]:px-4 [&_td]:py-3 [&_tr:hover]:bg-slate-50/70">
            {{ $slot }}
        </tbody>
    </table>
</div>
