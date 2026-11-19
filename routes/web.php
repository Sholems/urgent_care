<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// Admin portal
Route::redirect('/admin', '/admin/login');

// Public website
Route::controller(HomeController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/department', 'department')->name('department');
    Route::get('/doctors', 'doctors')->name('doctors');
    Route::get('/doctors/{name}', 'doctorCategoryWise')->name('doctors.category');
    Route::get('/doctor/{id}', 'doctor')->name('doctor');
    Route::post('/api/appointments/booked-slots', 'getBookedSlots');
    Route::post('/appointments/book', 'bookAppointment')->name('appointments.book');
    Route::get('/contact', 'contact')->name('contact');
    Route::post('/contact', 'contactStore')->name('contact.submit');
    Route::get('/privacy-policy', 'privacyPolicy')->name('privacy-policy');
});

Route::view('/care-for-families-abroad', 'families-abroad')
    ->name('homecare.families');

Route::view('/preview-features', 'preview-features')
    ->name('preview.features');

Route::fallback(function () {
    return view('404');
});