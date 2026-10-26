<?php

use App\Http\Controllers\Admin\LogoutController;
use App\Http\Controllers\AttachmentDownloadController;
use App\Livewire\Admin;
use Illuminate\Support\Facades\Route;

// Loaded with prefix "admin" and name "admin." (bootstrap/app.php). One role: every user is an Admin.

Route::middleware(['admin.locale', 'guest:web'])->group(function () {
    Route::get('login', Admin\Auth\Login::class)->name('login');
});

Route::middleware(['admin.locale', 'auth:web'])->group(function () {
    Route::post('logout', LogoutController::class)->name('logout');

    Route::get('/', Admin\Dashboard::class)->name('dashboard');
    Route::get('complaints', Admin\Complaints\Index::class)->name('complaints.index');
    Route::get('complaints/{complaint:tracking_number}', Admin\Complaints\Show::class)->name('complaints.show');
    Route::get('attachments/{attachment}', AttachmentDownloadController::class)->name('attachments.show');
});
