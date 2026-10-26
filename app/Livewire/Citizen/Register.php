<?php

namespace App\Livewire\Citizen;

use App\Livewire\Forms\CitizenForm;
use App\Services\CitizenAccountService;
use App\Support\Countries;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.public')]
#[Title('Create an account')]
class Register extends Component
{
    public CitizenForm $form;

    public function render()
    {
        return view('livewire.citizen.register', ['countries' => Countries::options()]);
    }

    public function register(CitizenAccountService $accounts)
    {
        $key = 'citizen-register:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('form.email', __('Too many attempts. Please try again in :minutes minutes.', [
                'minutes' => ceil(RateLimiter::availableIn($key) / 60),
            ]));

            return null;
        }

        RateLimiter::hit($key, 600);

        $this->form->normalizeAll();
        $data = $this->form->validate();
        unset($data['password_confirmation']);

        $citizen = $accounts->register($data);

        Auth::guard('citizen')->login($citizen);
        session()->regenerate();

        return $this->redirectRoute('citizen.complaints.index');
    }
}
