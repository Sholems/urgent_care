<?php

namespace App\Http\Controllers;

use App\Models\DailyVisit;
use Illuminate\Http\Request;

class DailyVisitController extends Controller {
  /**
   * Get daily visits for an admission
   */
  public function index($admissionId) {
    $visits = DailyVisit::where('admission_id', $admissionId)
      ->with('doctor')
      ->orderByDesc('visit_date')
      ->get();

    return response()->json([
      'daily_visits' => $visits,
    ]);
  }
}




