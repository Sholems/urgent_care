<?php

namespace App\Services;

use App\Models\AppUser;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Notification;
use Illuminate\Support\Str;

class AppointmentNotifier {
  public function status(Appointment $appointment, string $status): void {
    $appointment->loadMissing(['user', 'doctor']);

    $date = $appointment->date;
    $time = $appointment->time;
    $statusLabel = ucfirst($status);

    $templates = [
      'accepted' => [
        'user'   => "Your appointment on {$date} at {$time} has been approved.",
        'doctor' => "Appointment on {$date} at {$time} has been approved.",
      ],
      'declined' => [
        'user'   => "Your appointment on {$date} at {$time} has been declined.",
        'doctor' => "Appointment on {$date} at {$time} has been declined.",
      ],
      'pending' => [
        'user'   => "Your appointment on {$date} at {$time} is pending review.",
        'doctor' => "New appointment on {$date} at {$time} is awaiting your review.",
      ],
      'completed' => [
        'user'   => "Your appointment on {$date} at {$time} has been marked as completed.",
        'doctor' => "Appointment on {$date} at {$time} has been marked as completed.",
      ],
    ];

    $defaultMessage = "Appointment on {$date} at {$time} status updated to {$statusLabel}.";

    $userMessage = $templates[$status]['user'] ?? $defaultMessage;
    $doctorMessage = $templates[$status]['doctor'] ?? $defaultMessage;

    $this->storeNotification(AppUser::class, $appointment->user_id, $status, $userMessage, $appointment);
    $this->storeNotification(Doctor::class, $appointment->doctor_id, $status, $doctorMessage, $appointment);

    $this->sendPushNotifications($appointment, $statusLabel, $userMessage, $doctorMessage);
  }

  protected function storeNotification(string $notifiableType, ?int $notifiableId, string $status, string $message, Appointment $appointment): void {
    if (!$notifiableId) {
      return;
    }

    Notification::create([
      'id'              => (string) Str::uuid(),
      'type'            => 'appointment.' . $status,
      'notifiable_type' => $notifiableType,
      'notifiable_id'   => $notifiableId,
      'data'            => json_encode([
        'message'        => $message,
        'appointment_id' => $appointment->id,
        'status'         => $status,
        'date'           => $appointment->date,
        'time'           => $appointment->time,
      ]),
    ]);
  }

  protected function sendPushNotifications(Appointment $appointment, string $statusLabel, string $userMessage, string $doctorMessage): void {
    /** @var FirebaseMessagingService $messaging */
    $messaging = app(FirebaseMessagingService::class);

    $userToken = $appointment->user?->firebase_token;
    $doctorToken = $appointment->doctor?->firebase_token;

    $dataPayload = [
      'appointment_id' => $appointment->id,
      'status'         => strtolower($statusLabel),
      'date'           => $appointment->date,
      'time'           => $appointment->time,
    ];

    if ($userToken) {
      $messaging->send([$userToken], "Appointment {$statusLabel}", $userMessage, $dataPayload);
    }

    if ($doctorToken) {
      $messaging->send([$doctorToken], "Appointment {$statusLabel}", $doctorMessage, $dataPayload);
    }
  }
}
