<?php

namespace App\Orchid\Screens\Appointment;

use Orchid\Screen\Screen;

use Orchid\Screen\TD;
use Orchid\Screen\Sight;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Actions\Link;
use Illuminate\Support\Facades\Storage;
use App\Models\Appointment;
use App\Models\AppointmentDocument;
use Orchid\Support\Facades\Alert;
use Illuminate\Http\Request;
use App\Services\AppointmentMailer;
use App\Services\AppointmentNotifier;

class AppointmentDetailScreen extends Screen {

  public $appointment;

  public function query(Appointment $appointment): iterable {
    $this->appointment = $appointment->load(['documents', 'user', 'doctor']);
    return [
      'appointment' => $this->appointment,
      'documents' => $this->appointment->documents,
    ];
  }

  public function name(): ?string {
    return 'Appointment Detail';
  }

  public function commandBar(): iterable {
    return [

      Button::make('Accept')
        ->icon('check')
        ->method('accept')
        ->parameters([
          'appointment' => $this->appointment->id,
        ])
        ->canSee($this->appointment->status === 'pending')
        ->confirm('Are you sure you want to accept this appointment ?'),

      Button::make('Decline')
        ->icon('bs.x-circle')
        ->method('decline')
        ->parameters([
          'appointment' => $this->appointment->id,
        ])
        ->canSee($this->appointment->status === 'pending')
        ->confirm('Are you sure you want to decline this appointment ?'),

      Button::make('Mark Pending')
        ->icon('bs.arrow-counterclockwise')
        ->method('markPending')
        ->parameters([
          'appointment' => $this->appointment->id,
        ])
        ->canSee($this->appointment->status !== 'pending')
        ->confirm('Mark this appointment as pending?'),

      Button::make('Mark Completed')
        ->icon('bs.check2-circle')
        ->method('complete')
        ->parameters([
          'appointment' => $this->appointment->id,
        ])
        ->canSee($this->appointment->status === 'accepted')
        ->confirm('Mark this appointment as completed?'),

      Button::make('Remove')
        ->icon('trash')
        ->method('remove')
        ->parameters([
          'appointment' => $this->appointment->id,
        ])
        ->confirm(
          'If you delete this appointment, all related data will be deleted.'
        )
    ];
  }

  public function layout(): iterable {
    return [
      Layout::legend('appointment', [
        Sight::make('id', 'Appointment ID:')->render(function (Appointment $appointment) {
          return '#' . $appointment->id;
        }),
        Sight::make('date', 'Date:')->render(function (Appointment $appointment) {
          return $appointment->date;
        }),
        Sight::make('time', 'Time:')->render(function (Appointment $appointment) {
          return $appointment->time;
        }),
        Sight::make('status', 'Status:')->render(function (Appointment $appointment) {
          return ucfirst($appointment->status ?? 'pending');
        }),
        Sight::make('doctor_name', 'Doctor Name:')->render(function (Appointment $appointment) {
          return $appointment->doctor?->name ?? '—';
        }),
        Sight::make('patient_name', 'Patient Name:')->render(function (Appointment $appointment) {
          return $appointment->user->user_name;
        }),
        Sight::make('patient_email', 'Patient Email:')->render(function (Appointment $appointment) {
          return $appointment->user->user_email;
        }),
        Sight::make('patient_phone_number', 'Patient Phone Number:')->render(function (Appointment $appointment) {
          return $appointment->user->user_phone_number;
        }),
        Sight::make('patient_medical_history', 'Patient Medical History:')->render(function (Appointment $appointment) {
          return $appointment->user->medical_history;
        }),
        Sight::make('patient_insurance', 'Patient Insurance:')->render(function (Appointment $appointment) {
          return $appointment->user->insurance_details;
        }),
        Sight::make('patient_medical_history_attachment', 'Medical History File:')->render(function (Appointment $appointment) {
          $path = $appointment->user->medical_history_attachment;
          if (empty($path)) {
            return '—';
          }

          return Link::make('Download')
            ->href(Storage::disk('public')->url($path))
            ->target('_blank');
        }),
        Sight::make('patient_insurance_attachment', 'Insurance File:')->render(function (Appointment $appointment) {
          $path = $appointment->user->insurance_details_attachment;
          if (empty($path)) {
            return '—';
          }

          return Link::make('Download')
            ->href(Storage::disk('public')->url($path))
            ->target('_blank');
        }),
        Sight::make('bank_account_holder_name', 'Account Holder Name:')->render(function (Appointment $appointment) {
          return $appointment->bank_account_holder_name ?? '—';
        }),
        Sight::make('bank_name', 'Bank Name / Provider:')->render(function (Appointment $appointment) {
          return $appointment->bank_name ?? '—';
        }),
        Sight::make('bank_account_number', 'Account Number / Reference:')->render(function (Appointment $appointment) {
          return $appointment->bank_account_number ?? '—';
        }),
        Sight::make('payment_invoice_path', 'Payment Invoice:')->render(function (Appointment $appointment) {
          if (!$appointment->payment_invoice_path) {
            return '—';
          }

          return Link::make('Download Invoice')
            ->href(Storage::disk('public')->url($appointment->payment_invoice_path))
            ->target('_blank');
        }),
      ]),


      Layout::table('documents', [
        TD::make('type', 'Type'),
        TD::make('file_path', 'File')->render(function (AppointmentDocument $document) {
          return Link::make('Download')
            ->href(Storage::disk('public')->url($document->file_path))
            ->target('_blank');
        }),
      ]),

      Layout::rows([
        Select::make('document.type')
          ->title('Document Type')
          ->options([
            'prescription' => 'Prescription',
            'medical_report' => 'Medical Report',
            'other' => 'Other Document',
            'invoice' => 'Invoice',
          ])
          ->required(),
        Input::make('document.file')
          ->type('file')
          ->title('File')
          ->required(),
        Button::make('Upload Document')
          ->method('uploadDocument')
          ->icon('bs.upload'),
      ]),
    ];
  }

  public function accept(Appointment $appointment) {
    $appointment->status = 'accepted';
    $appointment->save();
    app(AppointmentNotifier::class)->status($appointment, 'accepted');
    app(AppointmentMailer::class)->sendStatus($appointment, 'accepted');
    Alert::info('Appointment accepted');
    return redirect()->route('platform.appointment.list');
  }

  public function decline(Appointment $appointment) {
    $appointment->status = 'declined';
    $appointment->save();
    app(AppointmentNotifier::class)->status($appointment, 'declined');
    app(AppointmentMailer::class)->sendStatus($appointment, 'declined');
    Alert::info('Appointment declined');
    return redirect()->route('platform.appointment.list');
  }

  public function markPending(Appointment $appointment) {
    $appointment->status = 'pending';
    $appointment->save();
    app(AppointmentNotifier::class)->status($appointment, 'pending');
    Alert::info('Appointment marked as pending');
    return redirect()->route('platform.appointment.list');
  }

  public function complete(Appointment $appointment) {
    $appointment->status = 'completed';
    $appointment->save();
    app(AppointmentNotifier::class)->status($appointment, 'completed');
    Alert::info('Appointment marked as completed');
    return redirect()->route('platform.appointment.list');
  }

  public function remove(Appointment $appointment) {
    $appointment->delete();
    Alert::info('Appointment deleted');
    return redirect()->route('platform.appointment.list');
  }

  public function uploadDocument(Appointment $appointment, Request $request) {
    $request->validate([
      'document.file' => 'required|file',
      'document.type' => 'required|in:prescription,medical_report,other,invoice',
    ]);

    $path = $request->file('document.file')->store('appointment_documents', 'public');

    $appointment->documents()->create([
      'type' => $request->input('document.type'),
      'file_path' => $path,
    ]);

    Alert::info('Document uploaded');

    return redirect()->route('platform.appointment.detail', $appointment->id);
  }
}
