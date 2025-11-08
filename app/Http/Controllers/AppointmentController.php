<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentDocument;
use App\Models\Notification;
use App\Models\AppUser;
use App\Models\Doctor;
use App\Models\StripeKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Storage;
use App\Services\AppointmentMailer;
use App\Services\AppointmentNotifier;

class AppointmentController extends Controller {

  public function __construct(private AppointmentMailer $mailer) {
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request) {
    $perPage = (int) $request->query('per_page', 15);
    $page    = (int) $request->query('page', 1);
    $perPage = max(1, min($perPage, 100));

    $paginator = Appointment::with(['doctor', 'documents'])
      ->orderByDesc('id')
      ->paginate($perPage, ['*'], 'page', $page);

    return $this->paginatedResponse($paginator);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create() {
    //
  }

  public function store(Request $request) {
    $validated = $request->validate([
        'date' => 'required|date',
        'time' => 'required|string',
        'user_id' => 'required',
        'doctor_id' => 'required|exists:doctors,id',
        'payment_intent_id' => 'nullable|string',
        'bank_account_holder_name' => 'nullable|string|max:255',
        'bank_name' => 'nullable|string|max:255',
        'bank_account_number' => 'nullable|string|max:255',
        'payment_invoice' => 'nullable|file',
    ]);

    $invoicePath = null;
    if ($request->hasFile('payment_invoice')) {
      $invoicePath = $request->file('payment_invoice')->store('appointment_invoices', 'public');
    }

    $appointment = Appointment::create([
        'date' => $validated['date'],
        'time' => $validated['time'],
        'user_id' => $validated['user_id'],
        'doctor_id' => $validated['doctor_id'],
        'status' => 'pending',
        'payment_intent_id' => $validated['payment_intent_id'] ?? null,
        'bank_account_holder_name' => $validated['bank_account_holder_name'] ?? null,
        'bank_name' => $validated['bank_name'] ?? null,
        'bank_account_number' => $validated['bank_account_number'] ?? null,
        'payment_invoice_path' => $invoicePath,
    ]);

    app(AppointmentNotifier::class)->status($appointment, 'pending');

    $this->mailer->sendCreated($appointment);

    return response()->json([
        'status' => 'success',
        'message' => 'Appointment created successfully',
        'data' => $appointment
    ], 201);
  }


  /**
   * Display the specified resource.
   */
  public function show(Request $request, $userId) {
    $perPage = (int) $request->query('per_page', 15);
    $page    = (int) $request->query('page', 1);
    $perPage = max(1, min($perPage, 100));

    $paginator = Appointment::with(['doctor', 'documents'])
      ->where('user_id', $userId)
      ->orderByDesc('id')
      ->paginate($perPage, ['*'], 'page', $page);

    return $this->paginatedResponse($paginator);
  }

  /**
   * Display appointments for a specific doctor.
   */
  public function doctorAppointments(Request $request, $doctorId) {
    $perPage = (int) $request->query('per_page', 15);
    $page    = (int) $request->query('page', 1);
    $perPage = max(1, min($perPage, 100));

    $paginator = Appointment::with(['doctor', 'user', 'review', 'documents'])
      ->where('doctor_id', $doctorId)
      ->orderByDesc('id')
      ->paginate($perPage, ['*'], 'page', $page);

    return $this->paginatedResponse($paginator);
  }

  /**
   * Get available half-hour slots for a doctor on a given date.
   */
public function availableSlots(Request $request, $doctorId) {
    $validated = $request->validate([
        'date' => 'required|date',
    ]);

    $doctor = Doctor::findOrFail($doctorId);
    $date   = $validated['date'];

    // Parse start/end no matter if stored as "09:00", "09:00:00", or "9:00 AM"
    $startTime = $doctor->start_time
        ? Carbon::parse($doctor->start_time)
        : Carbon::createFromTime(9, 0);

    $endTime = $doctor->end_time
        ? Carbon::parse($doctor->end_time)
        : Carbon::createFromTime(17, 0);

    // Anchor times to the requested date to avoid cross-day issues
    $startTime = Carbon::parse("$date ".$startTime->format('H:i:s'));
    $endTime   = Carbon::parse("$date ".$endTime->format('H:i:s'));

    if ($endTime->lte($startTime)) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Doctor end time must be after start time.',
        ], 422);
    }

    // Normalize booked times to "H:i" so comparisons match slot format
    $bookedTimes = Appointment::where('doctor_id', $doctorId)
        ->where('date', $date) // or ->whereDate('date', $date) if it's a DATETIME
        ->where('status', '!=', 'declined')
        ->pluck('time')
        ->map(function ($t) {
            try {
                return Carbon::parse($t)->format('H:i');
            } catch (\Throwable $e) {
                // Fallback for odd strings like "09:00:00"
                try {
                    return Carbon::createFromFormat('H:i:s', $t)->format('H:i');
                } catch (\Throwable $e2) {
                    return substr($t, 0, 5); // last resort
                }
            }
        })
        ->toArray();

    $slots = [];
    for ($time = $startTime->copy(); $time->lt($endTime); $time->addMinutes(30)) {
        $slot = $time->format('H:i');
        if (!in_array($slot, $bookedTimes, true)) {
            $slots[] = $slot;
        }
    }

    return response()->json([
        'status'  => 'success',
        'message' => 'Available slots fetched successfully',
        'data'    => $slots,
    ]);
}

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Appointment $appointment) {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Appointment $appointment) {
    //
  }

  /**
   * Reschedule an existing appointment.
   */
  public function reschedule(Request $request, $id) {
    $request->validate([
        'date' => 'required|date_format:Y-m-d',
        'time' => 'required|date_format:H:i'
    ]);

    $appointment = Appointment::findOrFail($id);
    $oldDate = $appointment->date;
    $oldTime = $appointment->time;

    $newDate = Carbon::createFromFormat('Y-m-d', $request->input('date'))->format('Y-m-d');
    $newTime = Carbon::createFromFormat('H:i', $request->input('time'))->format('H:i');

    $appointment->update([
        'date' => $newDate,
        'time' => $newTime
    ]);

    $data = [
      'message' => 'Appointment rescheduled to ' . $newDate . ' at ' . $newTime,
      'appointment_id' => $appointment->id,
      'old_date' => $oldDate,
      'old_time' => $oldTime,
    ];

    $this->notify(AppUser::class, $appointment->user_id, 'appointment.rescheduled', $data);
    $this->notify(Doctor::class, $appointment->doctor_id, 'appointment.rescheduled', $data);

    return response()->json([
        'status' => 'success',
        'message' => 'Appointment rescheduled successfully',
        'data' => $appointment
    ]);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy($id) {
    $appointment = Appointment::findOrFail($id);
    if ($appointment->payment_intent_id) {
      try {
        $key = StripeKey::first();
        $secret = $key?->secret_key;
        if ($secret) {
          $stripe = new StripeClient($secret);
          $stripe->refunds->create([
            'payment_intent' => $appointment->payment_intent_id,
          ]);
        }
      } catch (\Exception $e) {
        // Optionally log the exception or handle the refund failure
      }
    }

    if ($appointment->payment_invoice_path) {
      Storage::disk('public')->delete($appointment->payment_invoice_path);
    }

    $appointment->delete();

    $data = [
      'message' => 'Appointment on ' . $appointment->date . ' was cancelled',
      'appointment_id' => $appointment->id,
    ];

    $this->notify(AppUser::class, $appointment->user_id, 'appointment.canceled', $data);
    $this->notify(Doctor::class, $appointment->doctor_id, 'appointment.canceled', $data);

    return response()->json([
        'status' => 'success',
        'message' => 'Appointment canceled successfully'
    ]);
  }

  public function uploadDocument(Request $request, $id) {
    $request->validate([
      'file' => 'required|file',
      'type' => 'required|in:prescription,medical_report,other,invoice',
    ]);

    $appointment = Appointment::findOrFail($id);

    $path = $request->file('file')->store('appointment_documents', 'public');

    $document = $appointment->documents()->create([
      'type' => $request->input('type'),
      'file_path' => $path,
    ]);

    return response()->json([
      'status' => 'success',
      'message' => 'Document uploaded successfully',
      'data' => $document,
    ], 201);
  }

  private function notify($notifiableType, $notifiableId, $type, array $data) {
    Notification::create([
      'id' => (string) Str::uuid(),
      'type' => $type,
      'notifiable_type' => $notifiableType,
      'notifiable_id' => $notifiableId,
      'data' => json_encode($data),
    ]);
  }
}
