<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Illuminate\Support\Facades\Storage;

class VitalsReport extends Model {
  use HasFactory;
  use Filterable;
  use AsSource;

  protected $connection = 'mysql';
  protected $table = 'vitals_reports';

  protected $fillable = [
    'id',
    'service_request_id',
    'nurse_id',
    'vitals_data',
    'report_file_path',
    'uploaded_at',
  ];

  protected $casts = [
    'vitals_data' => 'array',
    'uploaded_at' => 'datetime',
  ];

  protected $allowedSorts = [
    'id',
    'uploaded_at',
    'created_at',
  ];

  public function serviceRequest() {
    return $this->belongsTo(ServiceRequest::class);
  }

  public function nurse() {
    return $this->belongsTo(Nurse::class);
  }

  public function getReportFileUrlAttribute() {
    if (!$this->report_file_path) {
      return null;
    }

    return Storage::disk('public')->url($this->report_file_path);
  }
}




