<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\EmailOtp;
use App\Mail\WelcomeMail;
use App\Services\SmtpConfigurator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

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

    $request->merge([
      'user_name'         => $request->has('user_name') ? trim($request->input('user_name')) : null,
      'user_email'        => $request->has('user_email') ? strtolower(trim($request->input('user_email'))) : null,
      'user_phone_number' => $request->has('user_phone_number') ? trim($request->input('user_phone_number')) : null,
    ]);

    $validated = $request->validate([
      'user_name'         => 'required|string',
      'user_email'        => 'nullable|email|unique:app_users,user_email|required_without:user_phone_number',
      'user_phone_number' => 'nullable|string|unique:app_users,user_phone_number|required_without:user_email',
      'user_password'     => 'required|string',
      'user_photo'        => 'nullable|image|max:5120',
    ]);

    $input = [
      'user_name'         => $validated['user_name'],
      'user_email'        => $validated['user_email'] ?? null,
      'user_phone_number' => $validated['user_phone_number'] ?? null,
      'user_password'     => Hash::make($validated['user_password']),
    ];

    if ($request->hasFile('user_photo')) {
      $input['user_photo'] = $request->file('user_photo')->store('app-users', 'public');
    }

    $appUser = AppUser::create($input);

    if ($appUser->user_email) {
      try {
        SmtpConfigurator::apply();
        Mail::to($appUser->user_email)->send(new WelcomeMail($appUser));
      } catch (\Throwable $e) {
        Log::warning('Failed to send welcome email', [
          'user_id' => $appUser->id,
          'email'   => $appUser->user_email,
          'message' => $e->getMessage(),
        ]);
      }
    }

    return response()->json([
      'message' => 'User created successfully',
      'user' => $appUser,
    ], 201); // 201 Created
  }


  // LOGIN //
  public function login(Request $request) {
    info($request->all());

    $request->merge([
      'user_email'        => $request->has('user_email') ? strtolower(trim($request->input('user_email'))) : null,
      'user_phone_number' => $request->has('user_phone_number') ? trim($request->input('user_phone_number')) : null,
    ]);

    $validated = $request->validate([
      'user_email'        => 'nullable|email|required_without:user_phone_number',
      'user_phone_number' => 'nullable|string|required_without:user_email',
      'user_password'     => 'required|string',
    ]);

    $user = AppUser::query()
      ->when($validated['user_email'] ?? null, fn($query, $email) => $query->where('user_email', $email))
      ->when(
        empty($validated['user_email']) && ($validated['user_phone_number'] ?? null),
        fn($query, $phone) => $query->where('user_phone_number', $phone)
      )
      ->first();

    if (!$user && ($validated['user_phone_number'] ?? null)) {
      $user = AppUser::where('user_phone_number', $validated['user_phone_number'])->first();
    }

    if (!$user || !Hash::check($validated['user_password'], $user->user_password)) {
      return response()->json([
        'message' => 'Invalid login credentials',
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
    $paginator = $user->appointments()
      ->with('doctor')
      ->orderByDesc('id')
      ->paginate($perPage, ['*'], 'page', $page);

    return $this->paginatedResponse($paginator);
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
      'user_photo'        => 'sometimes|image|max:5120',
    ]);

    if (array_key_exists('user_photo', $data)) {
      if (!empty($user->user_photo)) {
        Storage::disk('public')->delete($user->user_photo);
      }

      $data['user_photo'] = $data['user_photo']->store('app-users', 'public');
    }

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
