<?php

use App\Enums\ComplaintCategory;
use App\Models\Citizen;
use App\Models\Complaint;
use App\Models\Court;
use App\Models\User;
use App\Services\ComplaintSubmissionService;
use Database\Seeders\ChatbotSeeder;
use Database\Seeders\CourtSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(fn () => $this->seed([CourtSeeder::class, ChatbotSeeder::class]))
    ->in('Feature');

function citizen(array $attributes = []): Citizen
{
    static $n = 0;
    $n++;

    return Citizen::query()->create([
        'name' => 'Ahmed Khan',
        'email' => "citizen{$n}@example.com",
        'password' => 'password123',
        'country' => 'GB',
        'phone' => '+44770090'.str_pad((string) $n, 4, '0', STR_PAD_LEFT),
        'cnic_passport' => '82203-'.str_pad((string) $n, 7, '0', STR_PAD_LEFT).'-1',
        ...$attributes,
    ]);
}

function admin(): User
{
    return User::query()->create(['name' => 'Portal Admin', 'email' => 'admin'.uniqid().'@example.com', 'password' => 'password']);
}

function complaintData(array $overrides = []): array
{
    return [
        'court_id' => Court::query()->first()->id,
        'case_number' => 'CIVIL 214/2023',
        'case_title' => 'Ahmed Khan vs. Muhammad Aslam',
        'category' => ComplaintCategory::Delay->value,
        'description' => 'The case has been adjourned many times without any proceedings taking place.',
        ...$overrides,
    ];
}

function complaint(?Citizen $citizen = null, array $overrides = []): Complaint
{
    return app(ComplaintSubmissionService::class)->submit($citizen ?? citizen(), complaintData($overrides));
}
