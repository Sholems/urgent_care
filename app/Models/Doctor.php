<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Orchid\Screen\AsSource;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Filterable;
use App\Models\Review;

class Doctor extends Model {
  use AsSource;
  use Filterable;
  use HasFactory;

  protected $connection = 'mysql';
  protected $table = 'doctors';

  protected $hidden = ['password', 'created_at', 'updated_at'];

  protected $fillable = [
    'name',
    'price',
    'photo',
    'rating',
    'biography',
    'categories',
    'specialities',
    'certificates',
    'is_available',
    'start_time',
    'end_time',
    'promotion',
    'email',
    'password',
    'user_id',
  ];

  protected $casts = [
    'categories'    => 'array',
    'certificates'  => 'array',
    'is_available'  => 'boolean',
    'specialities'  => 'array',
  ];

  protected $allowedSorts = [
    'id',
    'name',
    'price',
    'speciality',
  ];

  protected $allowedFilters = [
    'id'             => Like::class,
    'name'           => Like::class,
    'price'          => Like::class,
    'speciality'     => Like::class,
    'is_available'   => Like::class,
  ];

  public function appointments() {
    return $this->hasMany(Appointment::class);
  }

  public function user() {
    return $this->belongsTo(User::class);
  }

  public function patients() {
    return $this
      ->hasManyThrough(AppUser::class, Appointment::class, 'doctor_id', 'id', 'id', 'user_id')
      ->distinct();
  }

  public function reviews() {
    return $this->hasMany(Review::class);
  }
}
