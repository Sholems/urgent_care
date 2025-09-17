<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// For Admin Portal
Route::redirect('/admin', '/admin/login');

// For Frontend

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
});

Route::fallback(function () {
    return view('404');
});



