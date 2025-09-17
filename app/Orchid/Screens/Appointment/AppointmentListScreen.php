<?php

namespace App\Orchid\Screens\Appointment;

use Orchid\Screen\Screen;

use App\Models\Appointment;
use App\Models\AppUser;
use App\Models\Notification;
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

    $builder = Appointment::query()
      ->with(['user'])
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
    $this->notifyUser($appointment, 'accepted');
    Alert::info('Appointment accepted');
    return redirect()->route('platform.appointment.list');
  }

  public function decline(Appointment $appointment) {
    $appointment->update(['status' => 'declined']);
    $this->notifyUser($appointment, 'declined');
    Alert::info('Appointment declined');
    return redirect()->route('platform.appointment.list');
  }

  protected function notifyUser(Appointment $appointment, string $status): void {
    $message = $status === 'accepted'
      ? 'Your appointment on ' . $appointment->date . ' at ' . $appointment->time . ' has been approved.'
      : 'Your appointment on ' . $appointment->date . ' at ' . $appointment->time . ' has been declined.';

    Notification::create([
      'id' => (string) Str::uuid(),
      'type' => 'appointment.' . $status,
      'notifiable_type' => AppUser::class,
      'notifiable_id' => $appointment->user_id,
      'data' => json_encode([
        'message' => $message,
        'appointment_id' => $appointment->id,
        'status' => $status,
        'date' => $appointment->date,
        'time' => $appointment->time,
      ]),
    ]);
  }
}
