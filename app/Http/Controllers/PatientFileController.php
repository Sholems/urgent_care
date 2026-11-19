<?php

namespace App\Http\Controllers;

use App\Models\PatientFile;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PatientFileController extends Controller {
  /**
   * Upload patient file
   */
  public function upload(Request $request) {
    $validated = $request->validate([
      'service_request_id' => 'required|exists:service_requests,id',
      'file_type' => 'required|in:vitals,medical_report,prescription,scan_result,other',
      'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:20480', // 20MB max
      'uploaded_by_type' => 'required|in:nurse,doctor',
      'uploaded_by_id' => 'required|integer',
      'description' => 'nullable|string',
    ]);

    $file = $request->file('file');
    $filePath = $file->store('patient-files', 'public');

    $patientFile = PatientFile::create([
      'service_request_id' => $validated['service_request_id'],
      'file_type' => $validated['file_type'],
      'file_path' => $filePath,
      'uploaded_by_type' => $validated['uploaded_by_type'],
      'uploaded_by_id' => $validated['uploaded_by_id'],
      'description' => $validated['description'] ?? null,
    ]);

    return response()->json([
      'message' => 'File uploaded successfully',
      'patient_file' => $patientFile,
    ], 201);
  }

  /**
   * Get all files for a request
   */
  public function getFiles($requestId) {
    $files = PatientFile::where('service_request_id', $requestId)
      ->orderByDesc('created_at')
      ->get()
      ->map(function($file) {
        return [
          'id' => $file->id,
          'file_type' => $file->file_type,
          'file_url' => $file->file_url,
          'uploaded_by_type' => $file->uploaded_by_type,
          'uploaded_by_id' => $file->uploaded_by_id,
          'description' => $file->description,
          'created_at' => $file->created_at,
        ];
      });

    return response()->json([
      'patient_files' => $files,
    ]);
  }

  /**
   * Delete file
   */
  public function delete($id) {
    $patientFile = PatientFile::findOrFail($id);

    // Delete physical file
    if ($patientFile->file_path && Storage::disk('public')->exists($patientFile->file_path)) {
      Storage::disk('public')->delete($patientFile->file_path);
    }

    $patientFile->delete();

    return response()->json([
      'message' => 'File deleted successfully',
    ]);
  }
}




