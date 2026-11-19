<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;

class RequestPayment extends Model {
  use HasFactory;
  use Filterable;
  use AsSource;

  protected $connection = 'mysql';
  protected $table = 'request_payments';

  protected $fillable = [
    'id',
    'service_request_id',
    'payment_type',
    'amount',
    'payment_status',
    'payment_intent_id',
    'payment_method',
    'payment_provider',
    'monime_checkout_session_id',
    'monime_payment_id',
    'monime_order_number',
    'provider_payload',
    'notes',
  ];

  protected $casts = [
    'provider_payload' => 'array',
  ];

  protected $allowedSorts = [
    'id',
    'payment_type',
    'amount',
    'payment_status',
    'created_at',
  ];

  public function serviceRequest() {
    return $this->belongsTo(ServiceRequest::class);
  }
}




