<?php

namespace App\Livewire\Citizen;

use App\Enums\ComplaintStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/** Citizen dashboard: counts by stage and every complaint registered against the account. */
#[Layout('components.layouts.citizen')]
#[Title('My complaints')]
class ComplaintList extends Component
{
    use WithPagination;

    public function render()
    {
        $citizen = Auth::guard('citizen')->user();

        $counts = $citizen->complaints()->toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $sum = fn (array $statuses) => collect($statuses)->sum(fn (ComplaintStatus $s) => $counts[$s->value] ?? 0);

        return view('livewire.citizen.complaint-list', [
            'complaints' => $citizen->complaints()->with(['court', 'latestReview'])->latest()->paginate(10),
            'stats' => [
                [__('Total'), $counts->sum()],
                [__('Pending'), $sum(ComplaintStatus::pending())],
                [__('In Progress'), $sum([ComplaintStatus::InProgress])],
                [__('Resolved'), $sum([ComplaintStatus::Resolved, ComplaintStatus::Closed])],
            ],
        ]);
    }
}
