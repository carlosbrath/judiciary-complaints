<?php

namespace App\Livewire\Citizen;

use App\Livewire\Auth\LoginComponent;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.public')]
#[Title('Citizen login')]
class Login extends LoginComponent
{
    protected function guard(): string
    {
        return 'citizen';
    }

    protected function homeUrl(): string
    {
        return route('citizen.complaints.index');
    }

    protected function viewData(): array
    {
        return [
            'heading' => __('Citizen login'),
            'subheading' => __('Log in to file a complaint and follow the ones you have filed.'),
            'citizen' => true,
        ];
    }
}
