<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment)
    {
    }

    public function build(): self
    {
        return $this->subject('We Received Your Appointment Request')
            ->view('emails.appointment_created')
            ->with([
                'appointment' => $this->appointment,
            ]);
    }
}

