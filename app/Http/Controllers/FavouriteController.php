<?php

namespace App\Http\Controllers;

use App\Models\Favourite;
use App\Models\Doctor;
use Illuminate\Http\Request;

class FavouriteController extends Controller {
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request, $userId) {
    $perPage = (int) $request->query('per_page', 15);
    $page    = (int) $request->query('page', 1);
    $perPage = max(1, min($perPage, 100));

    return Favourite::where('user_id', $userId)
      ->with('doctor')
      ->orderByDesc('id')
      ->paginate($perPage, ['*'], 'page', $page);
  }

  public function doctors(Request $request, $userId) {
    $perPage = (int) $request->query('per_page', 15);
    $page    = (int) $request->query('page', 1);
    $perPage = max(1, min($perPage, 100));

    $doctorIds = Favourite::where('user_id', $userId)->pluck('doctor_id');
    $query     = Doctor::whereIn('id', $doctorIds)->where('is_available', 1)->orderByDesc('id');

    $paginator = $query->paginate($perPage, ['*'], 'page', $page);
    $paginator->getCollection()->transform(function ($doctor) {
      $doctor->certificates = json_decode($doctor->certificates, true);
      return $doctor;
    });
    return $paginator;
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request) {
    $request->validate([
      'user_id'   => 'required|exists:app_users,id',
      'doctor_id' => 'required|exists:doctors,id',
    ]);

    $favourite = Favourite::firstOrCreate([
      'user_id'   => $request->user_id,
      'doctor_id' => $request->doctor_id,
    ]);

    return response()->json([
      'status'  => 'success',
      'message' => 'Favourite saved successfully',
      'data'    => $favourite,
    ], 201);
  }

  /**
   * Remove the specified favourite from storage.
   */
  public function destroy($userId, $doctorId) {
    $deleted = Favourite::where('user_id', $userId)
      ->where('doctor_id', $doctorId)
      ->delete();

    if ($deleted) {
      return response()->json([
        'status'  => 'success',
        'message' => 'Favourite removed successfully',
      ]);
    }

    return response()->json([
      'status'  => 'error',
      'message' => 'Favourite not found',
    ], 404);
  }
}
