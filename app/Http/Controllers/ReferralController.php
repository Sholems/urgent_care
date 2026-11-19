<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use App\Models\ServiceRequest;
use App\Services\ServiceRequestService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class ReferralController extends Controller {
  public function __construct(
    private ServiceRequestService $serviceRequestService,
    private NotificationService $notificationService
  ) {
  }

  /**
   * Refer to specialist
   */
  public function referSpecialist(Request $request, $id) {
    $validated = $request->validate([
      'gp_doctor_id' => 'required|exists:doctors,id',
      'specialist_doctor_id' => 'required|exists:doctors,id',
      'referral_notes' => 'nullable|string',
      'referral_data' => 'nullable|array',
    ]);

    $serviceRequest = ServiceRequest::findOrFail($id);

    $referral = Referral::create([
      'service_request_id' => $id,
      'gp_doctor_id' => $validated['gp_doctor_id'],
      'specialist_doctor_id' => $validated['specialist_doctor_id'],
      'referral_notes' => $validated['referral_notes'] ?? null,
      'referral_data' => $validated['referral_data'] ?? null,
      'status' => 'referred',
    ]);

    // Update request status
    $this->serviceRequestService->updateStatus($id, 'specialist_referred');

    // Assign specialist
    $assignment = $this->serviceRequestService->assignGP($id, $validated['specialist_doctor_id']);
    $assignment->update(['assigned_to_type' => 'specialist']);

    $serviceRequest->update(['status' => 'specialist_assigned']);

    // Notify specialist, user, and admin
    $this->notificationService->notifySpecialistReferral($serviceRequest, $referral);

    return response()->json([
      'message' => 'Specialist referral created successfully',
      'referral' => $referral->load(['gpDoctor', 'specialistDoctor']),
    ], 201);
  }

  /**
   * Get doctor's referrals
   */
  public function getDoctorReferrals($doctorId) {
    $perPage = (int) request()->query('per_page', 15);
    $page = (int) request()->query('page', 1);
    $perPage = max(1, min($perPage, 100));

    $referrals = Referral::where('specialist_doctor_id', $doctorId)
      ->orWhere('gp_doctor_id', $doctorId)
      ->with(['serviceRequest.user', 'gpDoctor', 'specialistDoctor'])
      ->orderByDesc('created_at')
      ->paginate($perPage, ['*'], 'page', $page);

    return $this->paginatedResponse($referrals);
  }

  /**
   * Accept referral
   */
  public function accept($id) {
    $referral = Referral::findOrFail($id);
    $referral->update(['status' => 'accepted']);

    $serviceRequest = ServiceRequest::findOrFail($referral->service_request_id);
    $serviceRequest->update(['status' => 'specialist_assigned']);

    return response()->json([
      'message' => 'Referral accepted',
      'referral' => $referral,
    ]);
  }

  /**
   * Reject referral
   */
  public function reject($id) {
    $referral = Referral::findOrFail($id);
    $referral->update(['status' => 'rejected']);

    return response()->json([
      'message' => 'Referral rejected',
      'referral' => $referral,
    ]);
  }
}




