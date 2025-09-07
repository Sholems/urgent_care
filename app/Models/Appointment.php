<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use App\Models\Review;
use App\Models\Doctor;
use App\Models\AppUser;
use App\Models\AppointmentDocument;

class Appointment extends Model {
  use AsSource;
  use HasFactory;
  use Filterable;

  protected $connection = 'mysql';
  protected $table = 'appointments';

  protected $fillable = [
    'id',
    'date',
    'time',
    'user_id',
    'doctor_id',
    'status',
    'payment_intent_id',
    'upcoming'

  ];

  protected $allowedSorts = [
    'id',
    'date',
    'time',
    'updated_at',
  ];

  protected $allowedFilters = [
    'id'         => Like::class,
    'date'       => Like::class,
    'time'       => Like::class,
    'updated_at' => Like::class,
  ];

  public function getUpcomingAttribute() {
    $appointmentDate = Carbon::parse($this->date);
    return Carbon::now()->lessThan($appointmentDate);
  }

  public function review() {
    return $this->hasOne(Review::class);
  }

  public function doctor() {
    return $this->belongsTo(Doctor::class);
  }

  public function user() {
    return $this->belongsTo(AppUser::class, 'user_id');
  }

  public function documents() {
    return $this->hasMany(AppointmentDocument::class);
  }

  public function prescriptions() {
    return $this->hasMany(AppointmentDocument::class)->where('type', 'prescription');
  }

  public function medicalReports() {
    return $this->hasMany(AppointmentDocument::class)->where('type', 'medical_report');
  }
}
