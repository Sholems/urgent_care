<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;

class Admission extends Model {
  use HasFactory;
  use Filterable;
  use AsSource;

  protected $connection = 'mysql';
  protected $table = 'admissions';

  protected $fillable = [
    'id',
    'service_request_id',
    'specialist_doctor_id',
    'admission_date',
    'expected_days',
    'actual_days',
    'discharge_date',
    'status',
  ];

  protected $casts = [
    'admission_date' => 'date',
    'discharge_date' => 'date',
  ];

  protected $allowedSorts = [
    'id',
    'admission_date',
    'discharge_date',
    'status',
    'created_at',
  ];

  public function serviceRequest() {
    return $this->belongsTo(ServiceRequest::class);
  }

  public function specialistDoctor() {
    return $this->belongsTo(Doctor::class, 'specialist_doctor_id');
  }

  public function dailyVisits() {
    return $this->hasMany(DailyVisit::class);
  }
}




