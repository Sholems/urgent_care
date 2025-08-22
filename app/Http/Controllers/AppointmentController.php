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

class AppointmentController extends Controller {
  /**
   * Display a listing of the resource.
   */
  public function index() {
    return Appointment::with(['doctor', 'documents'])->get();
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create() {
    //
  }

  public function store(Request $request) {
    $request->validate([
        'date' => 'required|date',
        'time' => 'required|string',
        'user_id' => 'required',
        'doctor_id' => 'required|exists:doctors,id',
        'status' => 'required|string',
        'payment_intent_id' => 'nullable|string'
    ]);

    $appointment = Appointment::create([
        'date' => $request->input('date'),
        'time' => $request->input('time'),
        'user_id' => $request->input('user_id'),
        'doctor_id' => $request->input('doctor_id'),
        'status' => $request->input('status'),
        'payment_intent_id' => $request->input('payment_intent_id'),
    ]);

    $this->notify(AppUser::class, $request->input('user_id'), 'appointment.created', [
      'message' => 'Appointment booked for ' . $request->input('date') . ' at ' . $request->input('time'),
      'appointment_id' => $appointment->id,
    ]);

    $this->notify(Doctor::class, $request->input('doctor_id'), 'appointment.created', [
      'message' => 'New appointment on ' . $request->input('date') . ' at ' . $request->input('time'),
      'appointment_id' => $appointment->id,
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Appointment created successfully',
        'data' => $appointment
    ], 201);
  }


  /**
   * Display the specified resource.
   */
  public function show($userId) {
    return Appointment::with(['doctor', 'documents'])->where('user_id', $userId)->get();
  }

  /**
   * Display appointments for a specific doctor.
   */
  public function doctorAppointments($doctorId) {
    return Appointment::with(['doctor', 'user', 'review', 'documents'])->where('doctor_id', $doctorId)->get();
  }

  /**
   * Get available half-hour slots for a doctor on a given date.
   */
  public function availableSlots(Request $request, $doctorId) {
    $request->validate([
      'date' => 'required|date',
    ]);

    $doctor = Doctor::findOrFail($doctorId);
    $date = $request->input('date');

    $startTime = $doctor->start_time ? Carbon::createFromFormat('H:i', $doctor->start_time) : Carbon::createFromTime(9, 0);
    $endTime = $doctor->end_time ? Carbon::createFromFormat('H:i', $doctor->end_time) : Carbon::createFromTime(17, 0);

    $bookedTimes = Appointment::where('doctor_id', $doctorId)
      ->where('date', $date)
      ->where('status', '!=', 'declined')
      ->pluck('time')
      ->toArray();

    $slots = [];
    for ($time = $startTime->copy(); $time < $endTime; $time->addMinutes(30)) {
      $slot = $time->format('H:i');
      if (!in_array($slot, $bookedTimes)) {
        $slots[] = $slot;
      }
    }

    return response()->json([
      'status' => 'success',
      'message' => 'Available slots fetched successfully',
      'data' => $slots,
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
