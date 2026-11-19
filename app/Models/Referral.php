<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;

class Referral extends Model {
  use HasFactory;
  use Filterable;
  use AsSource;

  protected $connection = 'mysql';
  protected $table = 'referrals';

  protected $fillable = [
    'id',
    'service_request_id',
    'gp_doctor_id',
    'specialist_doctor_id',
    'referral_notes',
    'referral_data',
    'status',
  ];

  protected $casts = [
    'referral_data' => 'array',
  ];

  protected $allowedSorts = [
    'id',
    'status',
    'created_at',
  ];

  public function serviceRequest() {
    return $this->belongsTo(ServiceRequest::class);
  }

  public function gpDoctor() {
    return $this->belongsTo(Doctor::class, 'gp_doctor_id');
  }

  public function specialistDoctor() {
    return $this->belongsTo(Doctor::class, 'specialist_doctor_id');
  }
}




