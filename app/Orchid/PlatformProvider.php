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

      Menu::make('Doctors')
        ->title('Navigation')
        ->icon('bs.person-circle')
        ->route('platform.doctor.list')
        ->permission('platform.systems.users'),

      Menu::make('Appointments')
        ->icon('bs.calendar')
        ->badge(function () {
          $user = Auth::user();

          $query = Appointment::query()->where('status', 'pending');

          if ($user?->doctor) {
            $query->where('doctor_id', $user->doctor->id);
          }

          $count = $query->count();

          return $count > 0 ? $count : null;
        })
        // ->badge(function () {
        //   if (Appointment::where('status', 'pending')->count() > 0) {
        //     return Appointment::where('status', 'pending')->count();
        //   }
        // })
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

      Menu::make('Banner')
        ->icon('bs.image')
        ->route('platform.banner.list')
        ->permission('platform.systems.users'),

     

      Menu::make('Categories')
        ->title('Details')
        ->icon('bs.grid')
        ->route('platform.category.list')
        ->permission('platform.systems.users'),

      Menu::make('Diagnostics')
        ->icon('bs.thermometer-half')
        ->route('platform.diagnostic.list')
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

      Menu::make(__('Roles'))
        ->icon('bs.shield')
        ->route('platform.systems.roles')
        ->permission('platform.systems.roles'),

       Menu::make('Stripe Keys')
       ->title('Configuration')
       ->icon('bs.credit-card')
        ->route('platform.stripekey.list')
        ->permission('platform.systems.users'),

        Menu::make('Twilio Settings')
        ->icon('bs.phone')
        ->route('platform.twilio.edit')
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
    ];
  }
}
