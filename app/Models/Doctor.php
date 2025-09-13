<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Orchid\Screen\AsSource;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Filterable;
use App\Models\Review;
use App\Models\AppUser;

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
      'experience',
      'biography',
      'categories',
      'specialities',
      'certificates',
      'is_available',
      'start_time',
      'end_time',
      'gender',
      'promotion',
      'email',
      'password',
      'user_id',
      'language',
      'city',
      'address',
      'country',
      'latitude',
      'longitude',
    ];

  protected $casts = [
    'categories'    => 'array',
    'certificates'  => 'array',
    'is_available'  => 'boolean',
    'specialities'  => 'array',
    'latitude'      => 'float',
    'longitude'     => 'float',
    'distance'      => 'float',
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

  // Doctor model
public function patients()
{
    return $this->belongsToMany(
        AppUser::class,   // related
        'appointments',              // pivot table
        'doctor_id',                 // FK on pivot pointing to doctors
        'user_id'                    // FK on pivot pointing to app_users
    );
}

  public function reviews() {
    return $this->hasMany(Review::class);
  }
}
