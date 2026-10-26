<?php

namespace App\Livewire\Public;

use App\Models\Complaint;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/** Status lookup without logging in: tracking number + the CNIC/passport it was filed under. */
#[Layout('components.layouts.public')]
#[Title('Track Complaint')]
class TrackComplaint extends Component
{
    public const MAX_ATTEMPTS = 10;

    #[Url(as: 'ref', except: '')]
    public string $trackingNumber = '';

    #[Url(as: 'id', except: '')]
    public string $identity = '';

    #[Locked]
    public ?int $complaintId = null;

    #[Locked]
    public bool $searched = false;

    public function mount(): void
    {
        if (filled($this->trackingNumber) && filled($this->identity)) {
            $this->track();
        }
    }

    public static function rateLimitKey(): string
    {
        return 'complaint-track:'.request()->ip();
    }

    public function track(): void
    {
        $this->validate([
            'trackingNumber' => ['required', 'string', 'max:20'],
            'identity' => ['required', 'string', 'max:20'],
        ], attributes: ['trackingNumber' => __('tracking number'), 'identity' => __('CNIC / passport number')]);

        $key = self::rateLimitKey();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $this->addError('trackingNumber', __('Too many attempts. Please try again in :minutes minutes.', [
                'minutes' => ceil(RateLimiter::availableIn($key) / 60),
            ]));

            return;
        }

        RateLimiter::hit($key, 600);

        $this->trackingNumber = strtoupper(trim($this->trackingNumber));
        $this->complaintId = Complaint::findForTracking($this->trackingNumber, $this->identity)?->id;
        $this->searched = true;
    }

    public function render()
    {
        return view('livewire.public.track-complaint', [
            'complaint' => $this->complaintId ? Complaint::query()->with(['court', 'reviews'])->find($this->complaintId) : null,
        ]);
    }
}
