<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;

class RequestAssignment extends Model {
  use HasFactory;
  use Filterable;
  use AsSource;

  protected $connection = 'mysql';
  protected $table = 'request_assignments';

  protected $fillable = [
    'id',
    'service_request_id',
    'assigned_to_type',
    'assigned_to_id',
    'assigned_at',
    'status',
  ];

  protected $casts = [
    'assigned_at' => 'datetime',
  ];

  protected $allowedSorts = [
    'id',
    'assigned_to_type',
    'assigned_at',
    'status',
    'created_at',
  ];

  public function serviceRequest() {
    return $this->belongsTo(ServiceRequest::class);
  }

  public function assignedTo() {
    if ($this->assigned_to_type === 'nurse') {
      return $this->belongsTo(Nurse::class, 'assigned_to_id');
    } elseif ($this->assigned_to_type === 'gp' || $this->assigned_to_type === 'specialist') {
      return $this->belongsTo(Doctor::class, 'assigned_to_id');
    }
    return null;
  }
}




