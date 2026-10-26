<?php

namespace App\Livewire\Citizen;

use App\Enums\ComplaintCategory;
use App\Livewire\Concerns\HandlesComplaintUploads;
use App\Livewire\Forms\ComplaintForm;
use App\Models\Complaint;
use App\Models\Court;
use App\Services\ComplaintSubmissionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.citizen')]
#[Title('File a Complaint')]
class FileComplaint extends Component
{
    use HandlesComplaintUploads;

    /** Shared with the chatbot, so using both doesn't double the allowance. */
    public const MAX_SUBMISSIONS = 5;
    public const DECAY_SECONDS = 600;

    public ComplaintForm $form;

    public function render()
    {
        return view('livewire.citizen.file-complaint', ['categories' => ComplaintCategory::options()]);
    }

    /** Courts grouped by district for the picker. */
    #[Computed]
    public function courts(): Collection
    {
        return Court::query()->active()->ordered()->get()->groupBy('district');
    }

    public static function rateLimitKey(): string
    {
        return 'complaint-submit:'.request()->ip();
    }

    public function submit(ComplaintSubmissionService $submissions)
    {
        $key = self::rateLimitKey();

        if (RateLimiter::tooManyAttempts($key, self::MAX_SUBMISSIONS)) {
            $this->addError('submit', __('Too many complaints from this connection. Please try again in :minutes minutes.', [
                'minutes' => ceil(RateLimiter::availableIn($key) / 60),
            ]));

            return null;
        }

        $this->validate($this->fileRules(), attributes: $this->fileAttributes());
        $data = $this->form->payload();

        RateLimiter::hit($key, self::DECAY_SECONDS);

        $complaint = $submissions->submit(Auth::guard('citizen')->user(), $data, $this->attachments, Complaint::VIA_FORM, request()->ip());

        session()->flash('status', __('Your complaint has been registered. Your tracking number is :number.', ['number' => $complaint->tracking_number]));

        return $this->redirectRoute('citizen.complaints.show', $complaint);
    }
}
