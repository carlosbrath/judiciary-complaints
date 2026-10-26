<?php

namespace App\Services;

use App\Models\Citizen;
use Illuminate\Validation\ValidationException;

/**
 * Finds or opens citizen accounts. Complaints filed through the chatbot are registered against the
 * account of the CNIC/passport given; registering later with the same CNIC/passport and WhatsApp
 * number claims that account (so the citizen sees those complaints on the dashboard).
 */
class CitizenAccountService
{
    /** @param array{name: string, country: string, phone: string, cnic_passport: string} $identity */
    public function forChat(array $identity): Citizen
    {
        $citizen = Citizen::query()->firstOrNew(['cnic_passport' => $identity['cnic_passport']]);

        // A registered citizen's own details are never overwritten from an anonymous chat.
        if (! $citizen->exists || $citizen->isUnclaimed()) {
            $citizen->fill($identity)->save();
        }

        return $citizen;
    }

    /** @param array{name: string, country: string, phone: string, cnic_passport: string, email: string, password: string} $data */
    public function register(array $data): Citizen
    {
        $existing = Citizen::query()->where('cnic_passport', $data['cnic_passport'])->first();

        if (! $existing) {
            return Citizen::query()->create($data);
        }

        if (! $existing->isUnclaimed()) {
            throw ValidationException::withMessages([
                'form.cnic_passport' => __('An account with this CNIC / passport number already exists. Please log in.'),
            ]);
        }

        if ($existing->phone !== $data['phone']) {
            throw ValidationException::withMessages([
                'form.phone' => __('Complaints were filed under this CNIC / passport with a different WhatsApp number. Please use that number to register.'),
            ]);
        }

        $existing->fill($data)->save();

        return $existing;
    }
}
