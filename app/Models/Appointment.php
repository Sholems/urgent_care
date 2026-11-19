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
use Illuminate\Support\Facades\Storage;

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
    'payment_provider',
    'monime_checkout_session_id',
    'monime_payment_id',
    'monime_order_number',
    'bank_account_holder_name',
    'bank_name',
    'bank_account_number',
    'payment_invoice_path',
    'upcoming',
  ];

  protected $appends = [
    'payment_invoice_url',
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

  public function getPaymentInvoiceUrlAttribute() {
    if (!$this->payment_invoice_path) {
      return null;
    }

    return Storage::disk('public')->url($this->payment_invoice_path);
  }
}
