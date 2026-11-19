<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\ServiceRequest;
use App\Models\DailyVisit;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdmissionController extends Controller {
  public function __construct(
    private NotificationService $notificationService
  ) {
  }

  /**
   * Create admission record
   */
  public function create(Request $request) {
    $validated = $request->validate([
      'service_request_id' => 'required|exists:service_requests,id',
      'specialist_doctor_id' => 'required|exists:doctors,id',
      'admission_date' => 'required|date',
      'expected_days' => 'nullable|integer|min:1',
    ]);

    $admission = Admission::create([
      'service_request_id' => $validated['service_request_id'],
      'specialist_doctor_id' => $validated['specialist_doctor_id'],
      'admission_date' => $validated['admission_date'],
      'expected_days' => $validated['expected_days'] ?? null,
      'status' => 'active',
    ]);

    $serviceRequest = ServiceRequest::findOrFail($validated['service_request_id']);
    $serviceRequest->update(['status' => 'admitted']);

    // Notify user and specialist
    $this->notificationService->notifyAdmissionCreated($serviceRequest, $admission);

    return response()->json([
      'message' => 'Admission created successfully',
      'admission' => $admission->load('specialistDoctor'),
    ], 201);
  }

  /**
   * Get admission details
   */
  public function show($id) {
    $admission = Admission::with([
      'serviceRequest.user',
      'specialistDoctor',
      'dailyVisits.doctor',
    ])->findOrFail($id);

    return response()->json([
      'admission' => $admission,
    ]);
  }

  /**
   * Record daily visit
   */
  public function recordDailyVisit(Request $request, $id) {
    $validated = $request->validate([
      'doctor_id' => 'required|exists:doctors,id',
      'visit_date' => 'required|date',
      'visit_notes' => 'nullable|string',
    ]);

    $admission = Admission::findOrFail($id);

    $dailyVisit = DailyVisit::create([
      'admission_id' => $id,
      'doctor_id' => $validated['doctor_id'],
      'visit_date' => $validated['visit_date'],
      'visit_notes' => $validated['visit_notes'] ?? null,
    ]);

    $serviceRequest = ServiceRequest::findOrFail($admission->service_request_id);

    // Notify user
    $this->notificationService->notifyDailyVisit($serviceRequest, $admission);

    return response()->json([
      'message' => 'Daily visit recorded successfully',
      'daily_visit' => $dailyVisit->load('doctor'),
    ], 201);
  }

  /**
   * Discharge patient
   */
  public function discharge(Request $request, $id) {
    $validated = $request->validate([
      'discharge_date' => 'nullable|date',
    ]);

    $admission = Admission::findOrFail($id);
    $dischargeDate = $validated['discharge_date'] ?? Carbon::now()->toDateString();

    // Calculate actual days
    $admissionDate = Carbon::parse($admission->admission_date);
    $actualDays = $admissionDate->diffInDays(Carbon::parse($dischargeDate));

    $admission->update([
      'discharge_date' => $dischargeDate,
      'actual_days' => $actualDays,
      'status' => 'discharge_pending',
    ]);

    $serviceRequest = ServiceRequest::findOrFail($admission->service_request_id);
    $serviceRequest->update(['status' => 'discharge_pending']);

    // Notify user
    $this->notificationService->notifyDischargeReady($serviceRequest, $admission);

    return response()->json([
      'message' => 'Patient marked for discharge',
      'admission' => $admission,
    ]);
  }

  /**
   * Complete discharge (after bills cleared)
   */
  public function completeDischarge($id) {
    $admission = Admission::findOrFail($id);
    $admission->update(['status' => 'discharged']);

    $serviceRequest = ServiceRequest::findOrFail($admission->service_request_id);
    $serviceRequest->update(['status' => 'discharged']);

    return response()->json([
      'message' => 'Discharge completed',
      'admission' => $admission,
    ]);
  }

  /**
   * Get patient admission history
   */
  public function getPatientAdmissions($requestId) {
    $admissions = Admission::where('service_request_id', $requestId)
      ->with(['specialistDoctor', 'dailyVisits.doctor'])
      ->orderByDesc('admission_date')
      ->get();

    return response()->json([
      'admissions' => $admissions,
    ]);
  }
}




