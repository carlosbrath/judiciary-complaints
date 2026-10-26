<?php

namespace App\Livewire\Admin\Complaints;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Services\ComplaintReviewService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** One complaint: details, citizen, attachments, and the review thread where the admin changes status and adds remarks. */
#[Layout('components.layouts.admin')]
class Show extends Component
{
    #[Locked]
    public Complaint $complaint;

    public string $status = '';

    public string $remark = '';

    public function mount(Complaint $complaint): void
    {
        $this->complaint = $complaint;
        $this->status = $complaint->status->value;
    }

    public function render()
    {
        return view('livewire.admin.complaints.show', [
            'complaint' => $this->complaint->load(['court', 'citizen', 'attachments', 'reviews.user']),
            'statuses' => ComplaintStatus::options(),
        ])->title($this->complaint->tracking_number);
    }

    public function saveReview(ComplaintReviewService $reviews): void
    {
        $this->validate([
            'status' => ['required', Rule::enum(ComplaintStatus::class)],
            'remark' => ['nullable', 'string', 'max:5000'],
        ]);

        $status = ComplaintStatus::from($this->status);

        if ($status === $this->complaint->status && blank($this->remark)) {
            $this->addError('remark', 'Write a remark or choose a new status.');

            return;
        }

        $review = $reviews->review($this->complaint, Auth::user(), $status, $this->remark);

        $this->complaint->refresh();
        $this->reset('remark');
        $this->status = $this->complaint->status->value;

        $this->dispatch('notify', message: $review->changesStatus()
            ? 'Status changed to '.$review->status_to->label().'.'
            : 'Review added. The citizen can see it now.');
    }
}
