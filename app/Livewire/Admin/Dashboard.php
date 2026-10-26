<?php

namespace App\Livewire\Admin;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\Court;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        $counts = Complaint::query()->toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $sum = fn (array $statuses) => collect($statuses)->sum(fn (ComplaintStatus $s) => (int) ($counts[$s->value] ?? 0));

        // [label, count, filter for the complaint list, hint]
        $cards = [
            ['Total complaints', (int) $counts->sum(), null, 'All time'],
            ['Pending', $sum(ComplaintStatus::pending()), 'pending', 'Submitted or under review'],
            ['In progress', $sum([ComplaintStatus::InProgress]), ComplaintStatus::InProgress->value, 'Being acted on'],
            ['Resolved', $sum([ComplaintStatus::Resolved, ComplaintStatus::Closed]), 'final', 'Resolved or closed'],
        ];

        $byStatus = collect(ComplaintStatus::cases())->map(fn (ComplaintStatus $s) => [
            'status' => $s, 'count' => (int) ($counts[$s->value] ?? 0),
        ]);

        $byCourt = Court::query()->withCount('complaints')->get()
            ->where('complaints_count', '>', 0)->sortByDesc('complaints_count')->take(6)->values();

        return view('livewire.admin.dashboard', [
            'cards' => $cards,
            'byStatus' => $byStatus,
            'byCourt' => $byCourt,
            'recent' => Complaint::query()->with(['court', 'citizen'])->latest()->limit(8)->get(),
        ]);
    }
}
