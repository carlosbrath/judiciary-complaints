<?php

namespace Database\Seeders;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Models\Citizen;
use App\Models\Court;
use App\Models\User;
use App\Services\ComplaintReviewService;
use App\Services\ComplaintSubmissionService;
use Illuminate\Database\Seeder;

/** Local-only sample data: a demo citizen (demo@example.com / password123) with a few complaints. */
class DemoSeeder extends Seeder
{
    public function run(ComplaintSubmissionService $submissions, ComplaintReviewService $reviews): void
    {
        if (Citizen::query()->where('email', 'demo@example.com')->exists()) {
            return;
        }

        $citizen = Citizen::query()->create([
            'name' => 'Ahmed Khan', 'email' => 'demo@example.com', 'password' => 'password123',
            'country' => 'GB', 'phone' => '+447700900123', 'cnic_passport' => '82203-1234567-1',
        ]);

        $admin = User::query()->first();
        $courts = Court::query()->ordered()->get();

        $samples = [
            ['Civil Suit 214/2023', 'Ahmed Khan vs. Muhammad Aslam', ComplaintCategory::Delay, ComplaintStatus::InProgress,
                'The suit about our family land has been pending since 2023. I live in Birmingham and every hearing is adjourned without proceedings.'],
            ['Family 77/2024', 'Ahmed Khan vs. Sajid Mehmood', ComplaintCategory::Documents, ComplaintStatus::UnderReview,
                'I applied for certified copies of the judgment three months ago through my attorney but they have not been issued yet.'],
            ['Execution 12/2022', 'Ahmed Khan vs. Bashir Ahmed', ComplaintCategory::Execution, ComplaintStatus::Resolved,
                'The decree in my favour was passed in 2022 but the execution petition has not been taken up by the court.'],
        ];

        foreach ($samples as $i => [$caseNumber, $title, $category, $status, $description]) {
            $complaint = $submissions->submit($citizen, [
                'court_id' => $courts[$i * 7 % $courts->count()]->id,
                'case_number' => $caseNumber,
                'case_title' => $title,
                'category' => $category->value,
                'description' => $description,
            ]);

            if (! $admin) {
                continue;
            }

            $path = [ComplaintStatus::UnderReview, ComplaintStatus::InProgress, ComplaintStatus::Resolved];
            $remarks = [
                'Your complaint has been received and forwarded to the Registrar for review.',
                'The presiding officer has been asked to fix the matter for regular hearing.',
                'The matter has been taken up and the citizen’s attorney has been informed.',
            ];

            foreach ($path as $step => $next) {
                $reviews->review($complaint->fresh(), $admin, $next, $remarks[$step]);

                if ($next === $status) {
                    break;
                }
            }
        }
    }
}
