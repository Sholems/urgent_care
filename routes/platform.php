<?php

declare(strict_types=1);

use App\Orchid\Screens\Examples\ExampleScreen;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

use App\Orchid\Screens\Category\CategoryListScreen;
use App\Orchid\Screens\Category\CategoryEditScreen;
use App\Orchid\Screens\Banner\BannerListScreen;
use App\Orchid\Screens\Banner\BannerEditScreen;
use App\Orchid\Screens\Promotion\PromotionListScreen;
use App\Orchid\Screens\Promotion\PromotionEditScreen;
use App\Orchid\Screens\Doctor\DoctorEditScreen;
use App\Orchid\Screens\Doctor\DoctorListScreen;
use App\Orchid\Screens\Speciality\SpecialityEditScreen;
use App\Orchid\Screens\Speciality\SpecialityListScreen;
use App\Orchid\Screens\Diagnostic\DiagnosticListScreen;
use App\Orchid\Screens\Diagnostic\DiagnosticEditScreen;
use App\Orchid\Screens\Appointment\AppointmentListScreen;
use App\Orchid\Screens\Appointment\AppointmentDetailScreen;
use App\Orchid\Screens\AppUser\AppUserListScreen;
use App\Orchid\Screens\Smtp\SmtpSettingScreen;
use App\Orchid\Screens\Twilio\TwilioSettingScreen;
use App\Orchid\Screens\StripeKey\StripeKeyListScreen;
use App\Orchid\Screens\StripeKey\StripeKeyEditScreen;
use App\Orchid\Screens\Monime\MonimeCredentialListScreen;
use App\Orchid\Screens\Monime\MonimeCredentialEditScreen;
use App\Orchid\Screens\SiteSetting\SiteSettingScreen;
use App\Orchid\Screens\AdminBank\AdminBankDetailScreen;
use App\Orchid\Screens\Pricing\TransportOptionListScreen;
use App\Orchid\Screens\Pricing\TransportOptionEditScreen;
use App\Orchid\Screens\Pricing\MealOptionListScreen;
use App\Orchid\Screens\Pricing\MealOptionEditScreen;
use App\Orchid\Screens\Pricing\ConsultationOptionListScreen;
use App\Orchid\Screens\Pricing\ConsultationOptionEditScreen;
use App\Orchid\Screens\Pricing\ServiceRequestAddonOptionListScreen;
use App\Orchid\Screens\Pricing\ServiceRequestAddonOptionEditScreen;
use App\Orchid\Screens\ServiceRequest\ServiceRequestListScreen;
use App\Orchid\Screens\ServiceRequest\ServiceRequestDetailScreen;
use App\Orchid\Screens\Nurse\NurseListScreen;
use App\Orchid\Screens\Nurse\NurseEditScreen;

//
// Dashboard Routes
//

// Doctors
Route::screen('doctor/list', DoctorListScreen::class)
  ->name('platform.doctor.list')
  // ->defaults('permission', 'platform.systems.users')
  ->middleware('check.orchid.permission');

Route::screen('doctor/edit/{doctor?}', DoctorEditScreen::class)
  ->name('platform.doctor.edit')
  // ->defaults('permission', 'platform.systems.users')
  ->middleware('check.orchid.permission');

// Appointments
Route::screen('appointment/list', AppointmentListScreen::class)
  ->name('platform.appointment.list');

Route::screen('appointment/edit/{appointment?}', AppointmentDetailScreen::class)
  ->name('platform.appointment.detail');

// Service Requests
Route::screen('service-request/list', ServiceRequestListScreen::class)
  ->name('platform.service-request.list')
  ->middleware('check.orchid.permission');

Route::screen('service-request/detail/{serviceRequest}', ServiceRequestDetailScreen::class)
  ->name('platform.service-request.detail')
  ->middleware('check.orchid.permission');

// Nurses
Route::screen('nurse/list', NurseListScreen::class)
  ->name('platform.nurse.list')
  ->middleware('check.orchid.permission');

Route::screen('nurse/edit/{nurse?}', NurseEditScreen::class)
  ->name('platform.nurse.edit')
  ->middleware('check.orchid.permission');

// Specialities
Route::screen('speciality/list', SpecialityListScreen::class)
  ->name('platform.speciality.list');

Route::screen('speciality/edit/{speciality?}', SpecialityEditScreen::class)
  ->name('platform.speciality.edit');

// Diagnostics
Route::screen('diagnostic/list', DiagnosticListScreen::class)
  ->name('platform.diagnostic.list');

Route::screen('diagnostic/edit/{diagnostic?}', DiagnosticEditScreen::class)
  ->name('platform.diagnostic.edit');

// Categories
Route::screen('category/list', CategoryListScreen::class)
  ->name('platform.category.list');

Route::screen('category/edit/{category?}', CategoryEditScreen::class)
  ->name('platform.category.edit');

// Banners
Route::screen('banner/list', BannerListScreen::class)
  ->name('platform.banner.list');

Route::screen('banner/edit/{banner?}', BannerEditScreen::class)
  ->name('platform.banner.edit');

// Stripe Keys are temporarily hidden from admin panel routes.
// Route::screen('stripekey/list', StripeKeyListScreen::class)
//   ->name('platform.stripekey.list');
//
// Route::screen('stripekey/edit/{stripeKey?}', StripeKeyEditScreen::class)
//   ->name('platform.stripekey.edit');

Route::screen('monime/list', MonimeCredentialListScreen::class)
  ->name('platform.monime.list');

Route::screen('monime/edit/{credential?}', MonimeCredentialEditScreen::class)
  ->name('platform.monime.edit');

Route::screen('settings/site', SiteSettingScreen::class)
  ->name('platform.site.settings')
  ->middleware('check.orchid.permission');

Route::screen('settings/bank-details', AdminBankDetailScreen::class)
  ->name('platform.bank.details')
  ->middleware('check.orchid.permission');

Route::screen('pricing/transport-options', TransportOptionListScreen::class)
  ->name('platform.transport-options.list')
  ->middleware('check.orchid.permission');

Route::screen('pricing/transport-options/edit/{transportOption?}', TransportOptionEditScreen::class)
  ->name('platform.transport-options.edit')
  ->middleware('check.orchid.permission');

Route::screen('pricing/meal-options', MealOptionListScreen::class)
  ->name('platform.meal-options.list')
  ->middleware('check.orchid.permission');

Route::screen('pricing/meal-options/edit/{mealOption?}', MealOptionEditScreen::class)
  ->name('platform.meal-options.edit')
  ->middleware('check.orchid.permission');

Route::screen('pricing/consultation-options', ConsultationOptionListScreen::class)
  ->name('platform.consultation-options.list')
  ->middleware('check.orchid.permission');

Route::screen('pricing/consultation-options/edit/{consultationOption?}', ConsultationOptionEditScreen::class)
  ->name('platform.consultation-options.edit')
  ->middleware('check.orchid.permission');

Route::screen('pricing/service-request-addons', ServiceRequestAddonOptionListScreen::class)
  ->name('platform.service-request-addon-options.list')
  ->middleware('check.orchid.permission');

Route::screen('pricing/service-request-addons/edit/{serviceRequestAddonOption?}', ServiceRequestAddonOptionEditScreen::class)
  ->name('platform.service-request-addon-options.edit')
  ->middleware('check.orchid.permission');

// AppUsers
Route::screen('appuser/list', AppUserListScreen::class)
  ->name('platform.appuser.list');

Route::screen('smtp/edit', SmtpSettingScreen::class)
  ->name('platform.smtp.edit');

Route::screen('twilio/edit', TwilioSettingScreen::class)
  ->name('platform.twilio.edit');

// Promotions
Route::screen('promotion/list', PromotionListScreen::class)
  ->name('platform.promotion.list');

Route::screen('promotion/edit/{promotion?}', PromotionEditScreen::class)
  ->name('platform.promotion.edit');

// Main dashboard
Route::screen('/main', PlatformScreen::class)
  ->name('platform.main');

// Profile
Route::screen('profile', UserProfileScreen::class)
  ->name('platform.profile')
  ->breadcrumbs(fn(Trail $trail) => $trail
    ->parent('platform.index')
    ->push(__('Profile'), route('platform.profile')));

// Users
Route::screen('users/{user}/edit', UserEditScreen::class)
  ->name('platform.systems.users.edit')
  ->breadcrumbs(fn(Trail $trail, $user) => $trail
    ->parent('platform.systems.users')
    ->push($user->name, route('platform.systems.users.edit', $user)));

Route::screen('users/create', UserEditScreen::class)
  ->name('platform.systems.users.create')
  ->breadcrumbs(fn(Trail $trail) => $trail
    ->parent('platform.systems.users')
    ->push(__('Create'), route('platform.systems.users.create')));

Route::screen('users', UserListScreen::class)
  ->name('platform.systems.users')
  ->breadcrumbs(fn(Trail $trail) => $trail
    ->parent('platform.index')
    ->push(__('Users'), route('platform.systems.users')));

// Roles
Route::screen('roles/{role}/edit', RoleEditScreen::class)
  ->name('platform.systems.roles.edit')
  ->middleware('check.orchid.permission');

Route::screen('roles/create', RoleEditScreen::class)
  ->name('platform.systems.roles.create')
  ->middleware('check.orchid.permission');

Route::screen('roles', RoleListScreen::class)
  ->name('platform.systems.roles')
  ->middleware('check.orchid.permission');

// Example
Route::screen('example', ExampleScreen::class)
  ->name('platform.example')
  ->breadcrumbs(fn(Trail $trail) => $trail
    ->parent('platform.index')
    ->push('Example Screen'));

// Route::screen('idea', Idea::class, 'platform.screens.idea');
