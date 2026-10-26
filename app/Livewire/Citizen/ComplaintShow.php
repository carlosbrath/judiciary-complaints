<?php

namespace App\Livewire\Citizen;

use App\Models\Complaint;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** One complaint of the logged-in citizen: details, status timeline and the admin's remarks (read-only). */
#[Layout('components.layouts.citizen')]
class ComplaintShow extends Component
{
    #[Locked]
    public Complaint $complaint;

    public function mount(Complaint $complaint): void
    {
        // Someone else's complaint looks the same as one that doesn't exist.
        abort_unless($complaint->citizen_id === Auth::guard('citizen')->id(), 404);

        $this->complaint = $complaint;
    }

    public function render()
    {
        return view('livewire.citizen.complaint-show', [
            'complaint' => $this->complaint->load(['court', 'attachments', 'reviews']),
        ])->title(__('Complaint :number', ['number' => $this->complaint->tracking_number]));
    }
}
