<?php

use App\Http\Controllers\AttachmentDownloadController;
use App\Http\Controllers\Citizen\LogoutController;
use App\Livewire\Citizen;
use Illuminate\Support\Facades\Route;

// Overseas citizens register, then file and follow their complaints.

Route::middleware('guest:citizen')->name('citizen.')->group(function () {
    Route::get('register', Citizen\Register::class)->name('register');
    Route::get('login', Citizen\Login::class)->name('login');
});

Route::middleware('auth:citizen')->prefix('my')->name('citizen.')->group(function () {
    Route::post('logout', LogoutController::class)->name('logout');
    Route::get('complaints', Citizen\ComplaintList::class)->name('complaints.index');
    Route::get('complaints/new', Citizen\FileComplaint::class)->name('complaints.create');
    Route::get('complaints/{complaint:tracking_number}', Citizen\ComplaintShow::class)->name('complaints.show');
    Route::get('attachments/{attachment}', AttachmentDownloadController::class)->name('attachments.show');
});
