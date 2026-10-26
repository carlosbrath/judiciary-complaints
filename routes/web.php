<?php

use App\Http\Controllers\LocaleController;
use App\Livewire\Public\ComplaintChatbot;
use App\Livewire\Public\TrackComplaint;
use Illuminate\Support\Facades\Route;

Route::view('/', 'public.home')->name('home');

Route::get('/lang/{locale}', LocaleController::class)->name('locale.switch');

// Status lookup with tracking number + CNIC/passport (no login), same check as the chatbot.
Route::get('/track', TrackComplaint::class)->name('complaints.track');

// The assistant as its own page (the floating chat window links here for "full screen").
Route::get('/chat', ComplaintChatbot::class)->name('chatbot');
