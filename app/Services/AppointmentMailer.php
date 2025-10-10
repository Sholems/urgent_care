<?php

namespace App\Services;

use App\Mail\AppointmentCreatedMail;
use App\Mail\AppointmentStatusMail;
use App\Models\Appointment;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AppointmentMailer
{
    public function sendCreated(Appointment $appointment): void
    {
        $this->send($appointment, new AppointmentCreatedMail($appointment));
    }

    public function sendStatus(Appointment $appointment, string $status): void
    {
        $this->send($appointment, new AppointmentStatusMail($appointment, $status));
    }

    protected function send(Appointment $appointment, Mailable $mailable): void
    {
        $appointment->loadMissing(['user', 'doctor']);

        $email = $appointment->user?->user_email;
        if (!$email) {
            return;
        }

        try {
            SmtpConfigurator::apply();
            Mail::to($email)->send($mailable);
        } catch (\Throwable $e) {
            Log::warning('Failed to send appointment email', [
                'appointment_id' => $appointment->id,
                'email' => $email,
                'message' => $e->getMessage(),
            ]);
        }
    }
}

