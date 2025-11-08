<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Orchid\Screen\AsSource;
use Illuminate\Notifications\Notifiable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Filterable;
use App\Models\Appointment;

class AppUser extends Model {
  use HasFactory;
  use Filterable;
  use AsSource;
  use Notifiable;

  protected $connection = 'mysql';
  protected $table = 'app_users';

  protected $fillable = [
    'id',
    'user_name',
    'user_email',
    'user_password',
    'user_location',
    'user_phone_number',
    'user_email_verified',
    'user_phone_verified',
    'user_photo',
    'stripe_customer_id',
    'medical_history',
    'insurance_details',
    'medical_history_attachment',
    'insurance_details_attachment',
    'firebase_token',
  ];

  protected $allowedSorts = [
    'id',
    'user_name',
    'user_email',
    'user_password',
    'user_location',
    'user_phone_number',
    'user_email_verified',
    'user_phone_verified',
    'user_photo',
    'stripe_customer_id',
    'medical_history',
    'insurance_details',
    'medical_history_attachment',
    'insurance_details_attachment',
    'updated_at',
    'firebase_token',
  ];

  protected $allowedFilters = [
    'id'                  => Like::class,
    'user_name'           => Like::class,
    'user_email'          => Like::class,
    'user_password'       => Like::class,
    'user_location'       => Like::class,
    'user_phone_number'   => Like::class,
    'user_email_verified' => Like::class,
    'user_phone_verified' => Like::class,
    'user_photo'          => Like::class,
    'stripe_customer_id'  => Like::class,
    'medical_history'     => Like::class,
    'insurance_details'   => Like::class,
    'medical_history_attachment'   => Like::class,
    'insurance_details_attachment' => Like::class,
    'updated_at'          => Like::class,
    'firebase_token'      => Like::class,
  ];

  public function appointments() {
    return $this->hasMany(Appointment::class, 'user_id');
  }
}
