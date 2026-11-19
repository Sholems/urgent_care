<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;

class PlatformProvider extends OrchidServiceProvider {
  /**
   * Bootstrap the application services.
   *
   * @param Dashboard $dashboard
   *
   * @return void
   */
  public function boot(Dashboard $dashboard): void {
    parent::boot($dashboard);

    // ...
  }

  /**
   * Register the application menu.
   *
   * @return Menu[]
   */
  public function menu(): array {
    return [
      Menu::make('Dashboard')
        ->title('Overview')
        ->icon('bs.speedometer2')
        ->route('platform.main')
        ->permission(['platform.systems.users', 'platform.index']),

      Menu::make('Doctors')
        ->title('Navigation')
        ->icon('bs.person-circle')
        ->route('platform.doctor.list')
        ->permission('platform.systems.users'),

      Menu::make('Appointments')
        ->icon('bs.calendar')
        ->route('platform.appointment.list')
        ->permission('platform.appointments')
        ->title(__('Access Controls')),

        Menu::make('Appointments')
        ->icon('bs.calendar')
        
        // ->badge(function () {
        //   if (Appointment::where('status', 'pending')->count() > 0) {
        //     return Appointment::where('status', 'pending')->count();
        //   }
        // })
        ->route('platform.appointment.list')
        ->permission('platform.systems.users'),

      Menu::make('Service Requests')
        ->icon('bs.heart-pulse')
        ->route('platform.service-request.list')
        ->permission(['platform.systems.users', 'platform.service-requests']),

      Menu::make('Nurses')
        ->icon('bs.person-badge')
        ->route('platform.nurse.list')
        ->permission('platform.systems.users'), // Only admins can see this

      Menu::make('Banner')
        ->icon('bs.image')
        ->route('platform.banner.list')
        ->permission('platform.systems.users'),

     

      Menu::make('Categories')
        ->title('Details')
        ->icon('bs.grid')
        ->route('platform.category.list')
        ->permission('platform.systems.users'),

      Menu::make('Specialities')
        ->icon('bs.award')
        ->route('platform.speciality.list')
        ->permission('platform.systems.users'),

      Menu::make('Promotions')
        ->icon('bs.gift')
        ->route('platform.promotion.list')
        ->permission('platform.systems.users'),

      Menu::make('App Users')
        ->icon('bs.person')
        ->route('platform.appuser.list')
        ->permission('platform.systems.users'),

      Menu::make(__('Users'))
        ->icon('bs.people')
        ->route('platform.systems.users')
        ->permission('platform.systems.users')
        ->title(__('Access Controls')),

      // Stripe key management is temporarily hidden from the admin panel.
      // Menu::make('Stripe Keys')
      //   ->title('Configuration')
      //   ->icon('bs.credit-card')
      //   ->route('platform.stripekey.list')
      //   ->permission('platform.systems.users'),

      Menu::make('Monime Credentials')
        ->title('Configuration')
        ->icon('bs.credit-card-2-front')
        ->route('platform.monime.list')
        ->permission('platform.systems.users'),

      Menu::make('Bank Details')
        ->icon('bs.wallet2')
        ->route('platform.bank.details')
        ->permission('platform.systems.users'),

      Menu::make('Transport Pricing')
        ->icon('bs.truck')
        ->route('platform.transport-options.list')
        ->permission('platform.systems.users'),

      Menu::make('Meal Options')
        ->icon('bs.cup-hot')
        ->route('platform.meal-options.list')
        ->permission('platform.systems.users'),

      Menu::make('Consultation Options')
        ->icon('bs.chat-dots')
        ->route('platform.consultation-options.list')
        ->permission('platform.systems.users'),

      Menu::make('Service Add-ons')
        ->icon('bs.plus-square')
        ->route('platform.service-request-addon-options.list')
        ->permission('platform.systems.users'),

      Menu::make('Twilio Settings')
        ->icon('bs.phone')
        ->route('platform.twilio.edit')
        ->permission('platform.systems.users'),

      Menu::make('Site Settings')
        ->icon('bs.gear')
        ->route('platform.site.settings')
        ->permission('platform.systems.users'),

      Menu::make('SMTP Settings')
        ->icon('bs.envelope')
        ->route('platform.smtp.edit')
        ->permission('platform.systems.users')
        ->divider(),


     
    ];
  }

  /**
   * Register permissions for the application.
   *
   * @return ItemPermission[]
   */
  public function permissions(): array {
    return [
      ItemPermission::group(__('System'))
        ->addPermission('platform.systems.roles', __('Roles'))
        ->addPermission('platform.systems.users', __('Users')),
      ItemPermission::group(__('Nurse'))
        ->addPermission('platform.service-requests', __('Service Requests'))
        ->addPermission('platform.nurses', __('Nurses')),
    ];
  }
}
