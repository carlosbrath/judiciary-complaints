<?php

use App\Enums\ComplaintStatus;
use App\Livewire\Admin\Auth\Login;
use App\Livewire\Admin\Complaints\Index;
use App\Livewire\Admin\Complaints\Show;
use App\Models\Court;
use Livewire\Livewire;

it('keeps the admin panel behind the admin login', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));

    // A logged-in citizen is not an admin.
    $this->actingAs(citizen(), 'citizen')->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
});

it('logs an admin in', function () {
    $admin = admin();

    Livewire::test(Login::class)->set('email', $admin->email)->set('password', 'password')->call('login')
        ->assertRedirect(route('admin.dashboard'));
});

it('shows the dashboard stat cards', function () {
    $a = complaint();
    $b = complaint();
    complaint();
    $admin = admin();
    app(\App\Services\ComplaintReviewService::class)->review($a, $admin, ComplaintStatus::InProgress, null);
    app(\App\Services\ComplaintReviewService::class)->review($b, $admin, ComplaintStatus::Resolved, 'Done.');

    $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()
        ->assertSeeInOrder(['Total complaints', '3', 'Pending', '1', 'In progress', '1', 'Resolved', '1']);
});

it('stays in English for an admin who picked Urdu on the public site', function () {
    complaint();

    $this->actingAs(admin())->withSession(['locale' => 'ur'])->get(route('admin.complaints.index'))
        ->assertOk()->assertSee('Submitted')->assertDontSee('جمع شدہ');
});

it('searches and filters complaints', function () {
    $courts = Court::query()->ordered()->get();
    $uk = complaint(citizen(['country' => 'GB', 'name' => 'Bilal Ahmed']), ['court_id' => $courts[0]->id, 'case_title' => 'Bilal vs. State']);
    $uae = complaint(citizen(['country' => 'AE']), ['court_id' => $courts[1]->id, 'case_title' => 'Zahid vs. Tariq']);
    $this->actingAs($admin = admin());
    app(\App\Services\ComplaintReviewService::class)->review($uae, $admin, ComplaintStatus::InProgress, null);

    $titles = fn ($component) => $component->viewData('complaints')->pluck('case_title')->all();

    expect($titles(Livewire::test(Index::class)->set('search', 'Bilal Ahmed')))->toBe(['Bilal vs. State'])
        ->and($titles(Livewire::test(Index::class)->set('search', $uae->tracking_number)))->toBe(['Zahid vs. Tariq'])
        ->and($titles(Livewire::test(Index::class)->set('status', 'in_progress')))->toBe(['Zahid vs. Tariq'])
        ->and($titles(Livewire::test(Index::class)->set('status', 'pending')))->toBe(['Bilal vs. State'])
        ->and($titles(Livewire::test(Index::class)->set('court', (string) $courts[0]->id)))->toBe(['Bilal vs. State'])
        ->and($titles(Livewire::test(Index::class)->set('country', 'AE')))->toBe(['Zahid vs. Tariq'])
        ->and($titles(Livewire::test(Index::class)->set('from', now()->addDay()->toDateString())))->toBe([])
        ->and($titles(Livewire::test(Index::class)->set('to', now()->toDateString())))->toHaveCount(2);
});

it('changes the status and adds follow-up reviews the citizen sees', function () {
    $citizen = citizen();
    $complaint = complaint($citizen);
    $this->actingAs(admin());

    Livewire::test(Show::class, ['complaint' => $complaint])
        ->call('saveReview')->assertHasErrors('remark')
        ->set('status', 'under_review')->set('remark', 'Received, under review.')->call('saveReview')->assertHasNoErrors()
        ->set('remark', 'Called the Reader of the court.')->call('saveReview')->assertHasNoErrors()
        ->set('status', 'resolved')->call('saveReview')->assertHasNoErrors()
        ->assertSee('Called the Reader of the court.');

    $complaint->refresh();
    expect($complaint->status)->toBe(ComplaintStatus::Resolved)
        ->and($complaint->resolved_at)->not->toBeNull()
        ->and($complaint->reviews)->toHaveCount(3)
        ->and($complaint->reviews[1]->changesStatus())->toBeFalse();

    $this->actingAs($citizen, 'citizen')->get(route('citizen.complaints.show', $complaint))
        ->assertSee('Received, under review.')->assertSee('Called the Reader of the court.')->assertSee('Resolved');
});
