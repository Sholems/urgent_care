<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use \App\Http\Controllers\DoctorController;
use \App\Http\Controllers\BannerController;
use \App\Http\Controllers\AppUserController;
use \App\Http\Controllers\CategoryController;
use \App\Http\Controllers\PromotionController;
use \App\Http\Controllers\SpecialityController;
use \App\Http\Controllers\DiagnosticController;
use \App\Http\Controllers\AppointmentController;
use \App\Http\Controllers\FavouriteController;
use \App\Http\Controllers\StripeKeyController;
use \App\Http\Controllers\NotificationController;
use \App\Http\Controllers\ReviewController;
use \App\Http\Controllers\OtpController;
use \App\Http\Controllers\PhoneOtpController;
use \App\Http\Controllers\PaymentController;
use \App\Http\Controllers\UserController;
use \App\Http\Controllers\DashboardController;

Route::middleware('authApi')->group(function () {
  Route::get('/banners', [BannerController::class, 'index']);
  Route::get('/categories', [CategoryController::class, 'index']);
  Route::get('/promotions', [PromotionController::class, 'index']);
  Route::get('/diagnostics', [DiagnosticController::class, 'index']);
  Route::get('/specialities', [SpecialityController::class, 'index']);

  Route::get('/stripe-keys', [StripeKeyController::class, 'show']);

  Route::get('/doctors/search', [DoctorController::class, 'search']);
  Route::get('/doctors/category/{category}', [DoctorController::class, 'categorySearch']);
  Route::get('/doctors', [DoctorController::class, 'index']);
  Route::get('/doctors/{doctorId}', [DoctorController::class, 'show']);
  Route::get('/doctors/{doctorId}/patients', [DoctorController::class, 'patients']);

  Route::get('/appointments', [AppointmentController::class, 'index']);
  Route::get('/appointments/{userId}', [AppointmentController::class, 'show']);
  Route::get('/appointments/doctor/{doctorId}', [AppointmentController::class, 'doctorAppointments']);
  Route::post('/appointments/{id}/reschedule', [AppointmentController::class, 'reschedule']);
  Route::post('/appointments/{id}/documents', [AppointmentController::class, 'uploadDocument']);
  Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']);

  // Notification routes
  Route::get('/notifications/{userId}', [NotificationController::class, 'index']);
  Route::get('/notifications/doctor/{doctorId}', [NotificationController::class, 'doctor']);
  Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

  Route::get('/user/{userId}', [AppUserController::class, 'show']);
  Route::get('/patients/{userId}/appointments', [AppUserController::class, 'appointments']);

  Route::get('/favourites/{userId}', [FavouriteController::class, 'index']);
  Route::get('/favourites/doctors/{userId}', [FavouriteController::class, 'doctors']);

  Route::post('/user/create', [AppUserController::class, 'store']);
  Route::post('/user/login', [AppUserController::class, 'login']);
  Route::post('/user/change-password', [AppUserController::class, 'changePassword']);
  Route::post('/doctor/login', [DoctorController::class, 'login']);
  Route::post('/doctor/edit/{doctor}', [DoctorController::class, 'update']);
  Route::post('/users/change-password', [UserController::class, 'changePassword']);

    Route::post('/otp/send', [OtpController::class, 'send']);
    Route::post('/otp/verify', [OtpController::class, 'verify']);
    Route::post('/phone-otp/send', [PhoneOtpController::class, 'send']);
    Route::post('/phone-otp/verify', [PhoneOtpController::class, 'verify']);

    Route::post('/appointments/create', [AppointmentController::class, 'store']);
  Route::post('/favourites/create', [FavouriteController::class, 'store']);
  Route::delete('/favourites/{userId}/{doctorId}', [FavouriteController::class, 'destroy']);
  Route::post('/reviews/create', [ReviewController::class, 'store']);
  Route::get('/reviews/doctor/{doctorId}', [ReviewController::class, 'doctorReviews']);

  Route::get('/dashboard/doctor/{doctorId}', [DashboardController::class, 'doctor']);
  Route::get('/dashboard/admin', [DashboardController::class, 'admin']);

  Route::post('/payments/payment-intent', [PaymentController::class, 'createPaymentIntent']);
  Route::post('/payments/setup-intent/{userId}', [PaymentController::class, 'createSetupIntent']);
  Route::get('/payments/methods/{userId}', [PaymentController::class, 'listPaymentMethods']);
  Route::delete('/payments/methods/{paymentMethodId}', [PaymentController::class, 'deletePaymentMethod']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});
