<?php

namespace App\Services;

use App\Models\ServiceRequest;
use App\Models\RequestAssignment;
use App\Models\Nurse;
use App\Models\Doctor;
use App\Services\ReferenceNumberService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class ServiceRequestService {
  public function __construct(
    private ReferenceNumberService $referenceNumberService,
    private NotificationService $notificationService
  ) {
  }

  /**
   * Create a new service request
   */
  public function create(array $data): ServiceRequest {
    $referenceNumber = $this->referenceNumberService->generate();

    $request = ServiceRequest::create([
      'reference_number' => $referenceNumber,
      'user_id' => $data['user_id'],
      'status' => 'pending',
      'transport_mode' => $data['transport_mode'],
      'patient_name' => $data['patient_name'],
      'patient_phone' => $data['patient_phone'],
      'patient_address' => $data['patient_address'],
      'patient_gender' => $data['patient_gender'],
      'symptoms' => $data['symptoms'] ?? null,
    ]);

    // Notify admin
    $this->notificationService->notifyRequestCreated($request);

    return $request;
  }

  /**
   * Assign a nurse to a request
   */
  public function assignNurse(int $requestId, int $nurseId): RequestAssignment {
    $request = ServiceRequest::findOrFail($requestId);
    $nurse = Nurse::findOrFail($nurseId);

    // Cancel any existing active nurse assignment
    RequestAssignment::where('service_request_id', $requestId)
      ->where('assigned_to_type', 'nurse')
      ->where('status', 'active')
      ->update(['status' => 'cancelled']);

    $assignment = RequestAssignment::create([
      'service_request_id' => $requestId,
      'assigned_to_type' => 'nurse',
      'assigned_to_id' => $nurseId,
      'assigned_at' => now(),
      'status' => 'active',
    ]);

    $request->update(['status' => 'nurse_assigned']);

    // Notify nurse and user
    $this->notificationService->notifyNurseAssigned($request, $nurse);

    return $assignment;
  }

  /**
   * Assign a GP to a request
   */
  public function assignGP(int $requestId, int $doctorId): RequestAssignment {
    $request = ServiceRequest::findOrFail($requestId);
    $doctor = Doctor::findOrFail($doctorId);

    // Cancel any existing active GP assignment
    RequestAssignment::where('service_request_id', $requestId)
      ->where('assigned_to_type', 'gp')
      ->where('status', 'active')
      ->update(['status' => 'cancelled']);

    $assignment = RequestAssignment::create([
      'service_request_id' => $requestId,
      'assigned_to_type' => 'gp',
      'assigned_to_id' => $doctorId,
      'assigned_at' => now(),
      'status' => 'active',
    ]);

    $request->update(['status' => 'gp_assigned']);

    // Notify GP and user
    $this->notificationService->notifyGPAssigned($request, $doctor);

    return $assignment;
  }

  /**
   * Update request status
   */
  public function updateStatus(int $requestId, string $status): ServiceRequest {
    $request = ServiceRequest::findOrFail($requestId);
    $request->update(['status' => $status]);

    // Notify relevant parties based on status
    $this->notificationService->notifyStatusUpdate($request, $status);

    return $request;
  }
}




