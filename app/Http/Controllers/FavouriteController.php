<?php

namespace App\Http\Controllers;

use App\Models\Favourite;
use Illuminate\Http\Request;

class FavouriteController extends Controller {
  /**
   * Display a listing of the resource.
   */
  public function index($userId) {
    return Favourite::where('user_id', $userId)
      ->with('doctor')
      ->get();
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
}
