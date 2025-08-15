<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Notification;
use App\Models\AppUser;
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

    Notification::create([
        'id' => (string) Str::uuid(),
        'type' => 'appointment.created',
        'notifiable_type' => AppUser::class,
        'notifiable_id' => $request->input('user_id'),
        'data' => json_encode([
            'message' => 'Appointment booked for ' . $request->input('date') . ' at ' . $request->input('time'),
            'appointment_id' => $appointment->id,
        ]),
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
   * Remove the specified resource from storage.
   */
  public function destroy($id) {
    $appointment = Appointment::findOrFail($id);
    $appointment->delete();

    Notification::create([
        'id' => (string) Str::uuid(),
        'type' => 'appointment.canceled',
        'notifiable_type' => AppUser::class,
        'notifiable_id' => $appointment->user_id,
        'data' => json_encode([
            'message' => 'Appointment on ' . $appointment->date . ' was cancelled',
            'appointment_id' => $appointment->id,
        ]),
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Appointment canceled successfully'
    ]);
  }
}
