<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Illuminate\Support\Facades\Storage;

class PatientFile extends Model {
  use HasFactory;
  use Filterable;
  use AsSource;

  protected $connection = 'mysql';
  protected $table = 'patient_files';

  protected $fillable = [
    'id',
    'service_request_id',
    'file_type',
    'file_path',
    'uploaded_by_type',
    'uploaded_by_id',
    'description',
  ];

  protected $allowedSorts = [
    'id',
    'file_type',
    'created_at',
  ];

  public function serviceRequest() {
    return $this->belongsTo(ServiceRequest::class);
  }

  public function getFileUrlAttribute() {
    if (!$this->file_path) {
      return null;
    }

    return Storage::disk('public')->url($this->file_path);
  }
}




