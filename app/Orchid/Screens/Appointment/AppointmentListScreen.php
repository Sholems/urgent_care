<?php

namespace App\Orchid\Screens\Appointment;

use Orchid\Screen\Screen;

use App\Models\Appointment;
use Orchid\Screen\TD;
use Illuminate\Support\Str;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\DropDown;

class AppointmentListScreen extends Screen {
  public function query(): iterable {
    $user = auth()->user();

    $appointments = $user?->doctor
      ? Appointment::where('doctor_id', $user->doctor->id)->get()
      : Appointment::all();

    return [
      'appointment' => $appointments,
    ];
  }

  public function name(): ?string {
    return 'Appointments';
  }

  public function commandBar(): iterable {
    return [];
  }

  public function layout(): iterable {
    return [
      Layout::table('appointment', [
        TD::make('id', 'ID')
          ->cantHide()
          ->sort()
          ->render(function (Appointment $appointment) {
            return '#' . $appointment->id;
          }),

        TD::make('date', 'Date')
          ->cantHide()
          ->sort()
          ->filter(Input::make())
          ->render(function (Appointment $appointment) {
            return $appointment->date;
          }),

        TD::make('time', 'Time')
          ->cantHide()
          ->sort()
          ->filter(Input::make())
          ->render(function (Appointment $appointment) {
            return $appointment->time;
          }),

        TD::make('user_name', 'Name')
          ->cantHide()
          ->sort()
          ->filter(Input::make())
          ->render(function (Appointment $appointment) {
            return $appointment->user->user_name;
          }),

        TD::make('user_email', 'Email')
          ->cantHide()
          ->sort()
          ->filter(Input::make())
          ->render(function (Appointment $appointment) {
            return $appointment->user->user_email;
          }),

       
        TD::make(__('Actions'))
          ->cantHide()
          ->align(TD::ALIGN_CENTER)
          ->render(fn (Appointment $appointment) => DropDown::make()
            ->icon('bs.three-dots-vertical')
            ->list([

              Link::make(__('Details'))
                ->route('platform.appointment.detail', $appointment->id)
                ->icon('bs.pencil'),

              Button::make('Delete')
                ->icon('bs.trash3')
                ->confirm('Are you sure you want to delete this appointment ?')
                ->method('remove')
                ->parameters([
                  'appointment' => $appointment->id,
                ]),

            ])),

      ])
    ];
  }

  public function remove(Appointment $appointment) {
    $appointment->delete();
    Alert::info('Appointment deleted');
    return redirect()->route('platform.appointment.list');
  }
}
