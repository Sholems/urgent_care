<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Notification;
use App\Models\AppUser;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AppointmentController extends Controller {
  /**
   * Display a listing of the resource.
   */
  public function index() {
    return Appointment::with('doctor')->get();
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
        'status' => 'required|string'
    ]);

    $appointment = Appointment::create([
        'date' => $request->input('date'),
        'time' => $request->input('time'),
        'user_id' => $request->input('user_id'),
        'doctor_id' => $request->input('doctor_id'),
        'status' => $request->input('status'),
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
    return Appointment::with('doctor')->where('user_id', $userId)->get();
  }

  /**
   * Display appointments for a specific doctor.
   */
  public function doctorAppointments($doctorId) {
    return Appointment::with('doctor')->where('doctor_id', $doctorId)->get();
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
        'date' => 'required|date',
        'time' => 'required|string'
    ]);

    $appointment = Appointment::findOrFail($id);
    $oldDate = $appointment->date;
    $oldTime = $appointment->time;

    $appointment->update([
        'date' => $request->input('date'),
        'time' => $request->input('time')
    ]);

    $data = [
      'message' => 'Appointment rescheduled to ' . $request->input('date') . ' at ' . $request->input('time'),
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
