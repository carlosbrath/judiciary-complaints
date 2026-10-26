<?php

namespace App\Livewire\Admin\Auth;

use App\Livewire\Auth\LoginComponent;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.auth')]
#[Title('Admin login')]
class Login extends LoginComponent
{
    protected function guard(): string
    {
        return 'web';
    }

    protected function homeUrl(): string
    {
        return route('admin.dashboard');
    }

    protected function viewData(): array
    {
        return [
            'heading' => 'Admin login',
            'subheading' => 'Sign in to review and respond to complaints.',
        ];
    }
}
