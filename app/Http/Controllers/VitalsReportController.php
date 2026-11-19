<?php

namespace App\Http\Controllers;

use App\Models\VitalsReport;
use App\Models\ServiceRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VitalsReportController extends Controller {
  public function __construct(
    private NotificationService $notificationService
  ) {
  }

  /**
   * Upload vitals report
   */
  public function uploadVitals(Request $request, $requestId) {
    $validated = $request->validate([
      'nurse_id' => 'required|exists:nurses,id',
      'vitals_data' => 'required|array',
      'vitals_data.*' => 'nullable', // Allow any vitals data structure
      'report_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
    ]);

    $serviceRequest = ServiceRequest::findOrFail($requestId);

    $reportFilePath = null;
    if ($request->hasFile('report_file')) {
      $file = $request->file('report_file');
      $reportFilePath = $file->store('vitals-reports', 'public');
    }

    $vitalsReport = VitalsReport::create([
      'service_request_id' => $requestId,
      'nurse_id' => $validated['nurse_id'],
      'vitals_data' => $validated['vitals_data'],
      'report_file_path' => $reportFilePath,
      'uploaded_at' => now(),
    ]);

    // Update request status
    $serviceRequest->update(['status' => 'vitals_uploaded']);

    // Notify GP and admin
    $this->notificationService->notifyVitalsUploaded($serviceRequest);

    return response()->json([
      'message' => 'Vitals report uploaded successfully',
      'vitals_report' => $vitalsReport->load('nurse'),
    ], 201);
  }

  /**
   * Get vitals reports for a request
   */
  public function getVitalsReports($requestId) {
    $reports = VitalsReport::where('service_request_id', $requestId)
      ->with('nurse')
      ->orderByDesc('uploaded_at')
      ->get();

    return response()->json([
      'vitals_reports' => $reports,
    ]);
  }
}




