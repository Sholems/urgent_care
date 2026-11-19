<?php

namespace App\Services;

use App\Models\ServiceRequest;
use Carbon\Carbon;

class ReferenceNumberService {
  /**
   * Generate a unique reference number
   * Format: LZ-{YYYYMMDD}-{6-digit-sequence}
   * Example: LZ-20250115-000001
   */
  public function generate(): string {
    $date = Carbon::now()->format('Ymd');
    $prefix = "LZ-{$date}-";

    // Get the last reference number for today
    $lastRequest = ServiceRequest::where('reference_number', 'like', $prefix . '%')
      ->orderBy('reference_number', 'desc')
      ->first();

    if ($lastRequest) {
      // Extract the sequence number from the last reference
      $lastSequence = (int) substr($lastRequest->reference_number, -6);
      $nextSequence = $lastSequence + 1;
    } else {
      $nextSequence = 1;
    }

    // Format with leading zeros (6 digits)
    $sequence = str_pad($nextSequence, 6, '0', STR_PAD_LEFT);

    return $prefix . $sequence;
  }
}




