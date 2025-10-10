<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment,
        public string $status
    ) {
    }

    public function build(): self
    {
        $statusLabel = ucfirst($this->status);
        $subject = $this->status === 'accepted'
            ? 'Your Appointment Has Been Accepted'
            : 'Update on Your Appointment';

        return $this->subject($subject)
            ->view('emails.appointment_status')
            ->with([
                'appointment' => $this->appointment,
                'status' => $this->status,
                'statusLabel' => $statusLabel,
            ]);
    }
}

