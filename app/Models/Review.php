<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Review extends Model
{
    use HasFactory;
    use AsSource;

    protected $fillable = [
        'appointment_id',
        'user_id',
        'doctor_id',
        'rating',
        'comment',
    ];

    /**
     * After a review is created, update the associated doctor's rating.
     */
    protected static function booted()
    {
        static::created(function (self $review) {
            $doctor = $review->doctor;
            if ($doctor) {
                $doctor->rating = $doctor->reviews()->avg('rating');
                $doctor->save();
            }
        });
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function user()
    {
        return $this->belongsTo(AppUser::class, 'user_id');
    }
}
