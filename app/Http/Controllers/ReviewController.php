<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Appointment;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $appointment = Appointment::findOrFail($request->input('appointment_id'));

        if ($appointment->status !== 'completed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only completed appointments can be reviewed',
            ], 422);
        }

        if ($appointment->review()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This appointment has already been reviewed',
            ], 409);
        }

        $review = Review::create([
            'appointment_id' => $appointment->id,
            'user_id' => $appointment->user_id,
            'doctor_id' => $appointment->doctor_id,
            'rating' => $request->input('rating'),
            'comment' => $request->input('comment'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Review submitted successfully',
            'data' => $review,
        ], 201);
    }

    public function doctorReviews($doctorId)
    {
        return Review::where('doctor_id', $doctorId)->with('user')->get();
    }
}
