<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/** The first Admin. Set ADMIN_EMAIL / ADMIN_PASSWORD in .env; otherwise a random password is printed. */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@ajkjudiciary.gov.pk');

        if (User::query()->where('email', $email)->exists()) {
            return;
        }

        $password = env('ADMIN_PASSWORD') ?: Str::password(14, symbols: false);

        User::query()->create(['name' => 'Portal Admin', 'email' => $email, 'password' => $password]);

        $this->command?->info("Admin account: {$email} / ".(env('ADMIN_PASSWORD') ? '(ADMIN_PASSWORD from .env)' : $password));
    }
}
