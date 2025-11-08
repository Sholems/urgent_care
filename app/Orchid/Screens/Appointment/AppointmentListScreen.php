<?php

namespace App\Orchid\Screens\Appointment;

use Orchid\Screen\Screen;

use App\Models\Appointment;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\DropDown;
use App\Services\AppointmentMailer;
use App\Services\AppointmentNotifier;

class AppointmentListScreen extends Screen {
  public function query(): iterable {
    $user = auth()->user();

    $builder = Appointment::query()
      ->with(['user', 'doctor'])
      ->defaultSort('updated_at', 'desc');

    if ($user?->doctor) {
      $builder->where('doctor_id', $user->doctor->id);
    }

    return [
      'appointment' => $builder->filters()->paginate(10),
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
            return Link::make('#' . $appointment->id)
              ->route('platform.appointment.detail', $appointment->id);
          }),

        TD::make('date', 'Date')
          ->cantHide()
          ->sort()
          ->filter(Input::make())
          ->render(function (Appointment $appointment) {
            return Link::make($appointment->date)
              ->route('platform.appointment.detail', $appointment->id);
          }),

        TD::make('time', 'Time')
          ->cantHide()
          ->sort()
          ->filter(Input::make())
          ->render(function (Appointment $appointment) {
            return Link::make($appointment->time)
              ->route('platform.appointment.detail', $appointment->id);
          }),

        TD::make('user_name', 'Name')
          ->cantHide()
          ->sort()
          ->filter(Input::make())
          ->render(function (Appointment $appointment) {
            return Link::make($appointment->user->user_name)
              ->route('platform.appointment.detail', $appointment->id);
          }),

        TD::make('user_email', 'Email')
          ->cantHide()
          ->sort()
          ->filter(Input::make())
          ->render(function (Appointment $appointment) {
            return Link::make($appointment->user->user_email)
              ->route('platform.appointment.detail', $appointment->id);
          }),

        TD::make('user_phone_number', 'Phone')
          ->cantHide()
          ->sort()
          ->filter(Input::make())
          ->render(function (Appointment $appointment) {
            $phone = $appointment->user->user_phone_number;

            if (!$phone) {
              return '—';
            }

            return Link::make($phone)
              ->route('platform.appointment.detail', $appointment->id);
          }),

        TD::make('doctor_name', 'Doctor')
          ->cantHide()
          ->sort()
          ->filter(Input::make())
          ->render(function (Appointment $appointment) {
            $doctorName = $appointment->doctor?->name;

            if (!$doctorName) {
              return '—';
            }

            return Link::make($doctorName)
              ->route('platform.appointment.detail', $appointment->id);
          }),

        TD::make('status', 'Status')
          ->cantHide()
          ->align(TD::ALIGN_CENTER)
          ->render(function (Appointment $appointment) {
            return ucfirst($appointment->status ?? 'pending');
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

              Button::make('Accept')
                ->icon('bs.check-circle')
                ->method('accept')
                ->parameters([
                  'appointment' => $appointment->id,
                ])
                ->confirm('Are you sure you want to accept this appointment?')
                ->canSee($appointment->status === 'pending'),

              Button::make('Decline')
                ->icon('bs.x-circle')
                ->method('decline')
                ->parameters([
                  'appointment' => $appointment->id,
                ])
                ->confirm('Are you sure you want to decline this appointment?')
                ->canSee($appointment->status === 'pending'),

              Button::make('Mark Pending')
                ->icon('bs.arrow-counterclockwise')
                ->method('markPending')
                ->parameters([
                  'appointment' => $appointment->id,
                ])
                ->confirm('Mark this appointment as pending?')
                ->canSee($appointment->status !== 'pending'),

              Button::make('Mark Completed')
                ->icon('bs.check2-circle')
                ->method('complete')
                ->parameters([
                  'appointment' => $appointment->id,
                ])
                ->confirm('Mark this appointment as completed?')
                ->canSee($appointment->status === 'accepted'),

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

  public function accept(Appointment $appointment) {
    $appointment->update(['status' => 'accepted']);
    app(AppointmentNotifier::class)->status($appointment, 'accepted');
    app(AppointmentMailer::class)->sendStatus($appointment, 'accepted');
    Alert::info('Appointment accepted');
    return redirect()->route('platform.appointment.list');
  }

  public function decline(Appointment $appointment) {
    $appointment->update(['status' => 'declined']);
    app(AppointmentNotifier::class)->status($appointment, 'declined');
    app(AppointmentMailer::class)->sendStatus($appointment, 'declined');
    Alert::info('Appointment declined');
    return redirect()->route('platform.appointment.list');
  }

  public function markPending(Appointment $appointment) {
    $appointment->update(['status' => 'pending']);
    app(AppointmentNotifier::class)->status($appointment, 'pending');
    Alert::info('Appointment marked as pending');
    return redirect()->route('platform.appointment.list');
  }

  public function complete(Appointment $appointment) {
    $appointment->update(['status' => 'completed']);
    app(AppointmentNotifier::class)->status($appointment, 'completed');
    Alert::info('Appointment marked as completed');
    return redirect()->route('platform.appointment.list');
  }
}
