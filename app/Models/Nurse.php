<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Illuminate\Notifications\Notifiable;

class Nurse extends Model {
  use HasFactory;
  use Filterable;
  use AsSource;
  use Notifiable;

  protected $connection = 'mysql';
  protected $table = 'nurses';

  protected $fillable = [
    'id',
    'user_id',
    'name',
    'phone',
    'email',
    'is_active',
    'firebase_token',
  ];

  protected $allowedSorts = [
    'id',
    'name',
    'phone',
    'email',
    'is_active',
    'created_at',
  ];

  protected $allowedFilters = [
    'id' => Like::class,
    'name' => Like::class,
    'phone' => Like::class,
    'email' => Like::class,
  ];

  public function user() {
    return $this->belongsTo(\Orchid\Platform\Models\User::class, 'user_id');
  }

  public function vitalsReports() {
    return $this->hasMany(VitalsReport::class);
  }

  public function assignments() {
    return $this->hasMany(RequestAssignment::class, 'assigned_to_id')
      ->where('assigned_to_type', 'nurse');
  }
}




