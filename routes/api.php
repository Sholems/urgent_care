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
use \App\Http\Controllers\ExchangeRateController;
use \App\Http\Controllers\AdminBankDetailController;
use \App\Http\Controllers\AdminContactController;
use \App\Http\Controllers\ServiceRequestController;
use \App\Http\Controllers\RequestPaymentController;
use \App\Http\Controllers\NurseController;
use \App\Http\Controllers\VitalsReportController;
use \App\Http\Controllers\PatientFileController;
use \App\Http\Controllers\ReferralController;
use \App\Http\Controllers\AdmissionController;
use \App\Http\Controllers\DailyVisitController;
use \App\Http\Controllers\MonimeController;
use \App\Http\Controllers\CartController;

Route::middleware('authApi')->group(function () {
  Route::get('/doctors/{doctor}/patients', [DoctorController::class, 'patients']);
  Route::get('/doctors/{doctor}/earning-stats', [DoctorController::class, 'earningStats']);
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


  Route::get('/appointments', [AppointmentController::class, 'index']);
  Route::get('/appointments/{userId}', [AppointmentController::class, 'show']);
  Route::get('/appointments/doctor/{doctorId}', [AppointmentController::class, 'doctorAppointments']);
  Route::get('/appointments/doctor/{doctorId}/available-slots', [AppointmentController::class, 'availableSlots']);
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
  Route::post('/user/find-or-create', [AppUserController::class, 'findOrCreate']);
  Route::post('/user/change-password', [AppUserController::class, 'changePassword']);
  Route::post('/user/update/{id}', [AppUserController::class, 'update']);
  Route::delete('/user/{id}', [AppUserController::class, 'destroy']);
  Route::post('/user/firebase-token', [AppUserController::class, 'saveFirebaseToken']);
  Route::post('/doctor/login', [DoctorController::class, 'login']);
  Route::post('/doctor/firebase-token', [DoctorController::class, 'saveFirebaseToken']);
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

  Route::get('/exchange-rate', [ExchangeRateController::class, 'show']);

  Route::get('/admin-bank-details', [AdminBankDetailController::class, 'show']);
  Route::get('/admin-contact', [AdminContactController::class, 'show']);

  Route::post('/payments/payment-intent', [PaymentController::class, 'createPaymentIntent']);
  Route::post('/payments/setup-intent/{userId}', [PaymentController::class, 'createSetupIntent']);
  Route::get('/payments/methods/{userId}', [PaymentController::class, 'listPaymentMethods']);
  Route::delete('/payments/methods/{paymentMethodId}', [PaymentController::class, 'deletePaymentMethod']);
  Route::post('/monime/checkout-sessions', [MonimeController::class, 'createCheckoutSession']);
  Route::get('/monime/checkout-sessions/{id}', [MonimeController::class, 'getCheckoutSession']);
  Route::get('/monime/payments/{id}', [MonimeController::class, 'getPayment']);
  Route::get('/monime/providers', [MonimeController::class, 'listProviders']);

  // Service Request routes
  Route::post('/service-requests/create', [ServiceRequestController::class, 'create']);
  Route::get('/service-requests/pricing-options', [ServiceRequestController::class, 'pricingOptions']);
  Route::get('/service-requests/{id}', [ServiceRequestController::class, 'show']);
  Route::get('/service-requests/user/{userId}', [ServiceRequestController::class, 'getUserRequests']);
  Route::put('/service-requests/{id}/status', [ServiceRequestController::class, 'updateStatus']);
  Route::get('/service-requests/admin', [ServiceRequestController::class, 'admin']);
  Route::get('/service-requests/nurse', [ServiceRequestController::class, 'nurseRequests']);
  Route::get('/service-requests/doctor/{doctorId}', [ServiceRequestController::class, 'doctorRequests']);
  Route::post('/service-requests/{id}/assign-nurse', [ServiceRequestController::class, 'assignNurse']);
  Route::post('/service-requests/{id}/assign-gp', [ServiceRequestController::class, 'assignGP']);
  Route::post('/service-requests/{id}/terminate', [ServiceRequestController::class, 'terminate']);

  // Request Payment routes
  Route::post('/service-requests/{id}/payment', [RequestPaymentController::class, 'createPayment']);
  Route::get('/service-requests/{id}/payments', [RequestPaymentController::class, 'getPayments']);
  Route::post('/service-requests/{id}/payment/confirm', [RequestPaymentController::class, 'confirmPayment']);
  Route::post('/service-requests/{id}/payment/meals', [RequestPaymentController::class, 'payForMeals']);

  // Nurse routes
  Route::post('/nurse/login', [NurseController::class, 'login']);
  Route::get('/nurses', [NurseController::class, 'index']);
  Route::get('/nurse/requests', [NurseController::class, 'getRequests']);
  Route::post('/nurse/requests/{id}/update-status', [NurseController::class, 'updateRequestStatus']);
  Route::post('/nurse/firebase-token', [NurseController::class, 'saveFirebaseToken']);

  // Vitals Report routes
  Route::post('/nurse/vitals/{requestId}', [VitalsReportController::class, 'uploadVitals']);
  Route::get('/vitals-reports/{requestId}', [VitalsReportController::class, 'getVitalsReports']);

  // Patient File routes
  Route::post('/patient-files/upload', [PatientFileController::class, 'upload']);
  Route::get('/patient-files/{requestId}', [PatientFileController::class, 'getFiles']);
  Route::delete('/patient-files/{id}', [PatientFileController::class, 'delete']);

  // Referral routes
  Route::post('/service-requests/{id}/refer-specialist', [ReferralController::class, 'referSpecialist']);
  Route::get('/doctors/referrals/{doctorId}', [ReferralController::class, 'getDoctorReferrals']);
  Route::post('/referrals/{id}/accept', [ReferralController::class, 'accept']);
  Route::post('/referrals/{id}/reject', [ReferralController::class, 'reject']);

  // Admission routes
  Route::post('/admissions/create', [AdmissionController::class, 'create']);
  Route::get('/admissions/{id}', [AdmissionController::class, 'show']);
  Route::post('/admissions/{id}/daily-visit', [AdmissionController::class, 'recordDailyVisit']);
  Route::post('/admissions/{id}/discharge', [AdmissionController::class, 'discharge']);
  Route::post('/admissions/{id}/complete-discharge', [AdmissionController::class, 'completeDischarge']);
  Route::get('/admissions/patient/{requestId}', [AdmissionController::class, 'getPatientAdmissions']);

  // Daily Visit routes
  Route::get('/admissions/{admissionId}/daily-visits', [DailyVisitController::class, 'index']);

  // Cart routes
  Route::get('/cart', [CartController::class, 'show']);
  Route::post('/cart/items', [CartController::class, 'addItem']);
  Route::put('/cart/items/{id}', [CartController::class, 'updateItem']);
  Route::delete('/cart/items/{id}', [CartController::class, 'removeItem']);
  Route::post('/cart/checkout', [CartController::class, 'checkout']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});

Route::post('/webhooks/monime', [MonimeController::class, 'webhook']);
