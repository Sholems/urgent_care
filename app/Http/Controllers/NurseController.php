<?php

namespace App\Http\Controllers;

use App\Models\Nurse;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class NurseController extends Controller {
  /**
   * Nurse login
   */
  public function login(Request $request) {
    $validated = $request->validate([
      'email' => 'required|email',
      'password' => 'required|string',
    ]);

    // Find nurse by email
    $nurse = Nurse::where('email', $validated['email'])->first();

    if (!$nurse || !$nurse->is_active) {
      throw ValidationException::withMessages([
        'email' => ['Invalid credentials or nurse account is inactive.'],
      ]);
    }

    // Get the associated Orchid user
    $user = $nurse->user;

    if (!$user || !Hash::check($validated['password'], $user->password)) {
      throw ValidationException::withMessages([
        'email' => ['Invalid credentials.'],
      ]);
    }

    return response()->json([
      'message' => 'Nurse logged in successfully',
      'nurse' => $nurse,
      'user' => $user,
    ]);
  }

  /**
   * Get nurse's assigned requests
   */
  public function getRequests(Request $request) {
    $nurseId = $request->input('nurse_id'); // Can be passed or from auth
    $nurse = Nurse::findOrFail($nurseId);

    $perPage = (int) $request->query('per_page', 15);
    $page = (int) $request->query('page', 1);
    $perPage = max(1, min($perPage, 100));

    $requests = ServiceRequest::whereHas('assignments', function($q) use ($nurse) {
      $q->where('assigned_to_type', 'nurse')
        ->where('assigned_to_id', $nurse->id)
        ->where('status', 'active');
    })
      ->with(['user', 'payments', 'vitalsReports'])
      ->orderByDesc('created_at')
      ->paginate($perPage, ['*'], 'page', $page);

    return $this->paginatedResponse($requests);
  }

  /**
   * Update request status (nurse)
   */
  public function updateRequestStatus(Request $request, $id) {
    $validated = $request->validate([
      'status' => 'required|in:nurse_assigned,vitals_uploaded,transporting,arrived_at_gp',
      'nurse_id' => 'required|exists:nurses,id',
    ]);

    $serviceRequest = ServiceRequest::findOrFail($id);
    $serviceRequest->update(['status' => $validated['status']]);

    return response()->json([
      'message' => 'Status updated successfully',
      'service_request' => $serviceRequest,
    ]);
  }

  /**
   * Save Firebase token for nurse
   */
  public function saveFirebaseToken(Request $request) {
    $validated = $request->validate([
      'nurse_id' => 'required|exists:nurses,id',
      'firebase_token' => 'required|string',
    ]);

    $nurse = Nurse::findOrFail($validated['nurse_id']);
    $nurse->update(['firebase_token' => $validated['firebase_token']]);

    return response()->json([
      'message' => 'Firebase token saved successfully',
    ]);
  }

  /**
   * Get all nurses (for assignment dropdowns)
   */
  public function index(Request $request) {
    $activeOnly = $request->query('active_only', false);
    
    $query = Nurse::query();
    if ($activeOnly) {
      $query->where('is_active', true);
    }
    
    $nurses = $query->orderBy('name')->get();
    
    return response()->json([
      'nurses' => $nurses,
    ]);
  }
}




