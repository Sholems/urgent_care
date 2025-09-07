<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\EmailOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AppUserController extends Controller {
  /**
   * Display a listing of the resource.
   */
  public function index() {
    //
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create() {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request) {
    info($request->all());

    $request->validate([
      'user_name' => 'required',
      'user_email' => 'required|email',
      'user_password' => 'required',
    ]);

    $userExists = AppUser::where('user_name', $request->user_name)
      ->orWhere('user_email', $request->user_email)
      ->exists();

    if ($userExists) {
      return response()->json([
        'message' => 'User with this name or email already exists',
      ], 409); // 409 Conflict
    }

    $input = $request->all();
    $input['user_password'] = Hash::make($request->user_password);

    $appUser = AppUser::create($input);

    return response()->json([
      'message' => 'User created successfully',
      'user' => $appUser,
    ], 201); // 201 Created
  }


  // LOGIN //
  public function login(Request $request) {
    info($request->all());

    $request->validate([
      'user_email' => 'required|email',
      'user_password' => 'required',
    ]);

    $user = AppUser::where('user_email', $request->user_email)->first();

    if (!$user || !Hash::check($request->user_password, $user->user_password)) {
      return response()->json([
        'message' => 'Invalid email or password',
      ], 401); // 401 Unauthorized
    }

    return response()->json([
      'message' => 'User logged in successfully',
      'user' => $user,
    ], 200); // 200 OK
  }

  // CHANGE PASSWORD //
  public function changePassword(Request $request) {
    info($request->all());

    $request->validate([
      'user_email'    => 'required|email',
      // 'otp'           => 'required',
      'new_password'  => 'required',
    ]);

    // $otpRecord = EmailOtp::where('email', $request->user_email)->first();

   

    $user = AppUser::where('user_email', $request->user_email)->first();

    if (!$user) {
      return response()->json([
        'message' => 'User not found',
      ], 404);
    }

    $user->user_password = Hash::make($request->new_password);
    $user->save();

    // $otpRecord->delete();

    return response()->json([
      'message' => 'Password changed successfully',
      'user'    => $user,
    ], 200); // 200 OK
  }

  /**
   * Display the specified resource.
  */
  public function show($id) {
    $user = AppUser::with('appointments.doctor')->find($id);

    if ($user === null) {
      return response()->json(['message' => 'User not found'], 404);
    }

    return response()->json($user);
  }

  /**
   * Fetch all appointments for the specified user.
   */
  public function appointments(Request $request, $userId) {
    $perPage = (int) $request->query('per_page', 15);
    $page    = (int) $request->query('page', 1);
    $perPage = max(1, min($perPage, 100));

    $user = AppUser::findOrFail($userId);

    return $user->appointments()
      ->with('doctor')
      ->orderByDesc('id')
      ->paginate($perPage, ['*'], 'page', $page);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(AppUser $appUser) {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, $id) {
    $user = AppUser::findOrFail($id);

    $data = $request->validate([
      'user_name'         => 'sometimes|string',
      'user_email'        => 'sometimes|email',
      'user_location'     => 'sometimes|string|nullable',
      'user_phone_number' => 'sometimes|string|nullable',
      'medical_history'   => 'sometimes|string|nullable',
      'insurance_details' => 'sometimes|string|nullable',
    ]);

    $user->fill($data);
    $user->save();

    return response()->json([
      'message' => 'User updated successfully',
      'user'    => $user,
    ]);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(AppUser $appUser) {
    //
  }
}
