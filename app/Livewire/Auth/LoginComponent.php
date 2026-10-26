<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

/** Shared email + password login with throttling. Subclasses pick the guard and where to go next. */
abstract class LoginComponent extends Component
{
    private const MAX_ATTEMPTS = 5;

    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    abstract protected function guard(): string;

    abstract protected function homeUrl(): string;

    abstract protected function viewData(): array;

    public function login()
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], attributes: ['email' => __('email'), 'password' => __('password')]);

        $key = Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $this->addError('email', __('auth.throttle', ['seconds' => RateLimiter::availableIn($key), 'minutes' => ceil(RateLimiter::availableIn($key) / 60)]));

            return null;
        }

        $credentials = ['email' => Str::lower(trim($this->email)), 'password' => $this->password];

        if (! Auth::guard($this->guard())->attempt($credentials, $this->remember)) {
            RateLimiter::hit($key);
            $this->addError('email', __('auth.failed'));
            $this->reset('password');

            return null;
        }

        RateLimiter::clear($key);
        session()->regenerate();

        return $this->redirectIntended($this->homeUrl());
    }

    public function render()
    {
        return view('livewire.auth.login', $this->viewData());
    }
}
