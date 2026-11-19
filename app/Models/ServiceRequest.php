<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;

class ServiceRequest extends Model {
  use HasFactory;
  use Filterable;
  use AsSource;

  protected $connection = 'mysql';
  protected $table = 'service_requests';

  protected $fillable = [
    'id',
    'reference_number',
    'user_id',
    'status',
    'transport_mode',
    'patient_name',
    'patient_phone',
    'patient_address',
    'patient_gender',
    'symptoms',
  ];

  protected $allowedSorts = [
    'id',
    'reference_number',
    'status',
    'created_at',
    'updated_at',
  ];

  protected $allowedFilters = [
    'id' => Like::class,
    'reference_number' => Like::class,
    'status' => Like::class,
    'patient_name' => Like::class,
    'patient_phone' => Like::class,
  ];

  public function user() {
    return $this->belongsTo(AppUser::class, 'user_id');
  }

  public function payments() {
    return $this->hasMany(RequestPayment::class);
  }

  public function vitalsReports() {
    return $this->hasMany(VitalsReport::class);
  }

  public function patientFiles() {
    return $this->hasMany(PatientFile::class);
  }

  public function referrals() {
    return $this->hasMany(Referral::class);
  }

  public function admissions() {
    return $this->hasMany(Admission::class);
  }

  public function assignments() {
    return $this->hasMany(RequestAssignment::class);
  }

  public function assignedNurse() {
    return $this->hasOne(RequestAssignment::class)
      ->where('assigned_to_type', 'nurse')
      ->where('status', 'active');
  }

  public function assignedGP() {
    return $this->hasOne(RequestAssignment::class)
      ->where('assigned_to_type', 'gp')
      ->where('status', 'active');
  }

  public function assignedSpecialist() {
    return $this->hasOne(RequestAssignment::class)
      ->where('assigned_to_type', 'specialist')
      ->where('status', 'active');
  }
}




