<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;

class DailyVisit extends Model {
  use HasFactory;
  use Filterable;
  use AsSource;

  protected $connection = 'mysql';
  protected $table = 'daily_visits';

  protected $fillable = [
    'id',
    'admission_id',
    'doctor_id',
    'visit_date',
    'visit_notes',
  ];

  protected $casts = [
    'visit_date' => 'date',
  ];

  protected $allowedSorts = [
    'id',
    'visit_date',
    'created_at',
  ];

  public function admission() {
    return $this->belongsTo(Admission::class);
  }

  public function doctor() {
    return $this->belongsTo(Doctor::class);
  }
}




