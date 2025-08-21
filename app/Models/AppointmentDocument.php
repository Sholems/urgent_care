<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class AppointmentDocument extends Model
{
    use HasFactory;
    use AsSource;

    protected $connection = 'mysql';
    protected $table = 'appointment_documents';

    protected $fillable = [
        'id',
        'appointment_id',
        'type',
        'file_path',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}

