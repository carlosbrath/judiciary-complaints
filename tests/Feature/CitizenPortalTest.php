<?php

use App\Enums\ComplaintStatus;
use App\Livewire\Citizen\FileComplaint;
use App\Livewire\Citizen\Login;
use App\Livewire\Citizen\Register;
use App\Models\Citizen;
use App\Models\Complaint;
use App\Models\Court;
use App\Services\CitizenAccountService;
use App\Services\ComplaintReviewService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('renders the public pages in English and Urdu', function () {
    $this->get('/')->assertOk()->assertSee('Overseas Judiciary Complaint Portal');
    $this->get(route('citizen.register'))->assertOk();
    $this->get(route('citizen.login'))->assertOk();
    $this->get(route('complaints.track'))->assertOk();

    $this->get(route('locale.switch', 'ur'))->assertRedirect();
    $this->get('/')->assertOk()->assertSee('dir="rtl"', false);
});

it('registers a citizen and normalises CNIC and phone', function () {
    Livewire::test(Register::class)
        ->set('form.name', 'Ahmed  Khan')
        ->set('form.country', 'GB')
        ->set('form.phone', '0044 7700 900123')
        ->set('form.cnic_passport', '8220312345671')
        ->set('form.email', 'Ahmed@Example.com')
        ->set('form.password', 'secret123')
        ->set('form.password_confirmation', 'secret123')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('citizen.complaints.index'));

    $citizen = Citizen::query()->sole();
    expect($citizen->only('name', 'phone', 'cnic_passport', 'email'))->toBe([
        'name' => 'Ahmed Khan', 'phone' => '+447700900123', 'cnic_passport' => '82203-1234567-1', 'email' => 'ahmed@example.com',
    ]);
    $this->assertAuthenticatedAs($citizen, 'citizen');
});

it('accepts a passport number and rejects bad identity values', function () {
    Livewire::test(Register::class)
        ->set('form.name', 'Sara Bibi')->set('form.country', 'AE')->set('form.phone', '12')
        ->set('form.cnic_passport', '12-3')->set('form.email', 'x')->set('form.password', 'short')
        ->call('register')
        ->assertHasErrors(['form.phone', 'form.cnic_passport', 'form.email', 'form.password']);

    Livewire::test(Register::class)
        ->set('form.name', 'Sara Bibi')->set('form.country', 'AE')->set('form.phone', '+971501234567')
        ->set('form.cnic_passport', 'ab 1234567')->set('form.email', 'sara@example.com')
        ->set('form.password', 'secret123')->set('form.password_confirmation', 'secret123')
        ->call('register')
        ->assertHasNoErrors();

    expect(Citizen::query()->sole()->cnic_passport)->toBe('AB1234567');
});

it('lets a citizen claim the account the chatbot opened, with the same WhatsApp number', function () {
    $opened = app(CitizenAccountService::class)->forChat([
        'name' => 'Ahmed Khan', 'country' => 'GB', 'phone' => '+447700900123', 'cnic_passport' => '82203-1234567-1',
    ]);
    complaint($opened);

    $register = fn (string $phone) => Livewire::test(Register::class)
        ->set('form.name', 'Ahmed Khan')->set('form.country', 'GB')->set('form.phone', $phone)
        ->set('form.cnic_passport', '82203-1234567-1')->set('form.email', 'ahmed@example.com')
        ->set('form.password', 'secret123')->set('form.password_confirmation', 'secret123')
        ->call('register');

    $register('+447700900999')->assertHasErrors('form.phone');
    $register('+447700900123')->assertHasNoErrors();

    expect(Citizen::query()->count())->toBe(1)
        ->and($opened->fresh()->email)->toBe('ahmed@example.com')
        ->and($opened->complaints()->count())->toBe(1);
});

it('logs a citizen in', function () {
    $citizen = citizen(['email' => 'demo@example.com']);

    Livewire::test(Login::class)->set('email', 'demo@example.com')->set('password', 'wrong')->call('login')->assertHasErrors('email');
    Livewire::test(Login::class)->set('email', 'demo@example.com')->set('password', 'password123')->call('login')
        ->assertRedirect(route('citizen.complaints.index'));

    $this->assertAuthenticatedAs($citizen, 'citizen');
});

it('requires login to file a complaint', function () {
    $this->get(route('citizen.complaints.create'))->assertRedirect(route('citizen.login'));
});

it('files a complaint with attachments and a unique tracking number', function () {
    Storage::fake('local');
    $citizen = citizen();
    $this->actingAs($citizen, 'citizen');
    $court = Court::query()->first();

    Livewire::test(FileComplaint::class)
        ->set('form.court_id', $court->id)
        ->set('form.case_number', 'civil 214/2023')
        ->set('form.case_title', 'Ahmed Khan vs. Muhammad Aslam')
        ->set('form.category', 'delay')
        ->set('form.description', 'The case has been adjourned twelve times without proceedings.')
        ->set('pendingAttachments', [UploadedFile::fake()->create('summons.pdf', 100, 'application/pdf')])
        ->set('form.declaration', true)
        ->call('submit')
        ->assertHasNoErrors();

    $complaint = Complaint::query()->sole();

    expect($complaint->tracking_number)->toBe(sprintf('OJC-%s-%05d', now()->year, $complaint->id))
        ->and($complaint->citizen_id)->toBe($citizen->id)
        ->and($complaint->case_number)->toBe('CIVIL 214/2023')
        ->and($complaint->status)->toBe(ComplaintStatus::Submitted)
        ->and($complaint->attachments)->toHaveCount(1);

    Storage::disk('local')->assertExists($complaint->attachments->first()->path);
});

it('validates the complaint form', function () {
    $this->actingAs(citizen(), 'citizen');

    Livewire::test(FileComplaint::class)
        ->set('form.court_id', 9999)
        ->set('form.case_number', '<script>')
        ->set('form.description', 'too short')
        ->set('form.category', 'nope')
        ->call('submit')
        ->assertHasErrors(['form.court_id', 'form.case_number', 'form.case_title', 'form.category', 'form.description', 'form.declaration']);

    expect(Complaint::query()->count())->toBe(0);
});

it('shows the citizen their complaints, timeline and the admin remarks', function () {
    $citizen = citizen();
    $complaint = complaint($citizen);
    app(ComplaintReviewService::class)->review($complaint, admin(), ComplaintStatus::UnderReview, 'Forwarded to the Registrar.');

    $this->actingAs($citizen, 'citizen');

    $this->get(route('citizen.complaints.index'))->assertOk()->assertSee($complaint->tracking_number)->assertSee('Forwarded to the Registrar.');
    $this->get(route('citizen.complaints.show', $complaint))->assertOk()
        ->assertSee('Under Review')->assertSee('Forwarded to the Registrar.')->assertSee('Court administration');
});

it('hides other citizens’ complaints and files', function () {
    Storage::fake('local');
    $other = complaint();
    $attachment = $other->attachments()->create(['path' => 'complaints/x.pdf', 'original_name' => 'x.pdf', 'mime' => 'application/pdf', 'size' => 1]);
    Storage::disk('local')->put('complaints/x.pdf', 'pdf');

    $this->actingAs(citizen(), 'citizen');

    $this->get(route('citizen.complaints.show', $other))->assertNotFound();
    $this->get(route('citizen.attachments.show', $attachment))->assertNotFound();
});

it('tracks a complaint publicly only with the matching CNIC/passport', function () {
    $citizen = citizen(['cnic_passport' => '82203-1234567-1']);
    $complaint = complaint($citizen);

    $this->get(route('complaints.track', ['ref' => strtolower($complaint->tracking_number), 'id' => '8220312345671']))
        ->assertOk()->assertSee($complaint->court->name_en);

    $this->get(route('complaints.track', ['ref' => $complaint->tracking_number, 'id' => '11111-1111111-1']))
        ->assertOk()->assertSee('No complaint found');
});
