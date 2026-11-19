<?php

namespace App\Services;

use App\Models\ServiceRequest;
use App\Models\RequestPayment;
use App\Models\Referral;
use App\Models\Admission;
use App\Models\Nurse;
use App\Models\Doctor;
use App\Models\AppUser;
use App\Models\Notification;
use App\Services\FirebaseMessagingService;
use Illuminate\Support\Str;

class NotificationService {
  public function __construct(
    private FirebaseMessagingService $messaging
  ) {
  }

  /**
   * Notify when request is created
   */
  public function notifyRequestCreated(ServiceRequest $request): void {
    $message = "New service request {$request->reference_number} has been created.";
    
    // Notify admin (get all admin users)
    $adminUsers = \Orchid\Platform\Models\User::whereHas('roles', function($q) {
      $q->where('slug', 'administrator');
    })->get();

    foreach ($adminUsers as $admin) {
      Notification::create([
        'id' => (string) Str::uuid(),
        'type' => 'service_request.created',
        'notifiable_type' => \Orchid\Platform\Models\User::class,
        'notifiable_id' => $admin->id,
        'data' => json_encode([
          'message' => $message,
          'service_request_id' => $request->id,
          'reference_number' => $request->reference_number,
        ]),
      ]);
    }

    // Send push to admin (if they have firebase tokens)
    // Note: Admin users might not have firebase tokens, so this is optional
  }

  /**
   * Notify when payment is confirmed
   */
  public function notifyPaymentConfirmed(ServiceRequest $request, RequestPayment $payment): void {
    $request->loadMissing('user');
    $message = "Payment for {$payment->payment_type} has been confirmed for request {$request->reference_number}.";

    // Notify user
    if ($request->user) {
      $this->storeNotification(AppUser::class, $request->user_id, 'payment_confirmed', $message, $request);
      if ($request->user->firebase_token) {
        $this->messaging->send(
          [$request->user->firebase_token],
          'Payment Confirmed',
          $message,
          ['service_request_id' => $request->id, 'payment_type' => $payment->payment_type]
        );
      }
    }

    // Notify admin
    $adminUsers = \Orchid\Platform\Models\User::whereHas('roles', function($q) {
      $q->where('slug', 'administrator');
    })->get();

    foreach ($adminUsers as $admin) {
      Notification::create([
        'id' => (string) Str::uuid(),
        'type' => 'service_request.payment_confirmed',
        'notifiable_type' => \Orchid\Platform\Models\User::class,
        'notifiable_id' => $admin->id,
        'data' => json_encode([
          'message' => $message,
          'service_request_id' => $request->id,
          'reference_number' => $request->reference_number,
        ]),
      ]);
    }

    // Notify assigned nurse if exists
    $nurseAssignment = $request->assignedNurse;
    if ($nurseAssignment) {
      $nurse = Nurse::find($nurseAssignment->assigned_to_id);
      if ($nurse && $nurse->firebase_token) {
        $this->messaging->send(
          [$nurse->firebase_token],
          'Payment Confirmed',
          $message,
          ['service_request_id' => $request->id]
        );
      }
    }

    // Notify assigned GP if exists
    $gpAssignment = $request->assignedGP;
    if ($gpAssignment) {
      $gp = Doctor::find($gpAssignment->assigned_to_id);
      if ($gp && $gp->firebase_token) {
        $this->messaging->send(
          [$gp->firebase_token],
          'Payment Confirmed',
          $message,
          ['service_request_id' => $request->id]
        );
      }
    }
  }

  /**
   * Notify when nurse is assigned
   */
  public function notifyNurseAssigned(ServiceRequest $request, Nurse $nurse): void {
    $request->loadMissing('user');
    $message = "Nurse {$nurse->name} has been assigned to your request {$request->reference_number}.";

    // Notify user
    if ($request->user) {
      $this->storeNotification(AppUser::class, $request->user_id, 'nurse_assigned', $message, $request);
      if ($request->user->firebase_token) {
        $this->messaging->send(
          [$request->user->firebase_token],
          'Nurse Assigned',
          $message,
          ['service_request_id' => $request->id]
        );
      }
    }

    // Notify nurse
    if ($nurse->firebase_token) {
      $nurseMessage = "You have been assigned to service request {$request->reference_number}.";
      $this->messaging->send(
        [$nurse->firebase_token],
        'New Assignment',
        $nurseMessage,
        ['service_request_id' => $request->id]
      );
    }
  }

  /**
   * Notify when GP is assigned
   */
  public function notifyGPAssigned(ServiceRequest $request, Doctor $doctor): void {
    $request->loadMissing('user');
    $message = "Dr. {$doctor->name} has been assigned to your request {$request->reference_number}.";

    // Notify user
    if ($request->user) {
      $this->storeNotification(AppUser::class, $request->user_id, 'gp_assigned', $message, $request);
      if ($request->user->firebase_token) {
        $this->messaging->send(
          [$request->user->firebase_token],
          'GP Assigned',
          $message,
          ['service_request_id' => $request->id]
        );
      }
    }

    // Notify GP
    if ($doctor->firebase_token) {
      $gpMessage = "You have been assigned to service request {$request->reference_number}.";
      $this->messaging->send(
        [$doctor->firebase_token],
        'New Assignment',
        $gpMessage,
        ['service_request_id' => $request->id]
      );
    }
  }

  /**
   * Notify when vitals are uploaded
   */
  public function notifyVitalsUploaded(ServiceRequest $request): void {
    $message = "Vitals report has been uploaded for request {$request->reference_number}.";

    // Notify assigned GP
    $gpAssignment = $request->assignedGP;
    if ($gpAssignment) {
      $gp = Doctor::find($gpAssignment->assigned_to_id);
      if ($gp && $gp->firebase_token) {
        $this->messaging->send(
          [$gp->firebase_token],
          'Vitals Report Available',
          $message,
          ['service_request_id' => $request->id]
        );
      }
    }

    // Notify admin
    $adminUsers = \Orchid\Platform\Models\User::whereHas('roles', function($q) {
      $q->where('slug', 'administrator');
    })->get();

    foreach ($adminUsers as $admin) {
      Notification::create([
        'id' => (string) Str::uuid(),
        'type' => 'service_request.vitals_uploaded',
        'notifiable_type' => \Orchid\Platform\Models\User::class,
        'notifiable_id' => $admin->id,
        'data' => json_encode([
          'message' => $message,
          'service_request_id' => $request->id,
        ]),
      ]);
    }
  }

  /**
   * Notify when specialist is referred
   */
  public function notifySpecialistReferral(ServiceRequest $request, Referral $referral): void {
    $request->loadMissing('user');
    $specialist = Doctor::find($referral->specialist_doctor_id);
    
    $message = "You have been referred to Dr. {$specialist->name} for request {$request->reference_number}.";

    // Notify user
    if ($request->user) {
      $this->storeNotification(AppUser::class, $request->user_id, 'specialist_referred', $message, $request);
      if ($request->user->firebase_token) {
        $this->messaging->send(
          [$request->user->firebase_token],
          'Specialist Referral',
          $message,
          ['service_request_id' => $request->id, 'referral_id' => $referral->id]
        );
      }
    }

    // Notify specialist
    if ($specialist && $specialist->firebase_token) {
      $specialistMessage = "You have received a referral for service request {$request->reference_number}.";
      $this->messaging->send(
        [$specialist->firebase_token],
        'New Referral',
        $specialistMessage,
        ['service_request_id' => $request->id, 'referral_id' => $referral->id]
      );
    }

    // Notify admin
    $adminUsers = \Orchid\Platform\Models\User::whereHas('roles', function($q) {
      $q->where('slug', 'administrator');
    })->get();

    foreach ($adminUsers as $admin) {
      Notification::create([
        'id' => (string) Str::uuid(),
        'type' => 'service_request.specialist_referred',
        'notifiable_type' => \Orchid\Platform\Models\User::class,
        'notifiable_id' => $admin->id,
        'data' => json_encode([
          'message' => $message,
          'service_request_id' => $request->id,
          'referral_id' => $referral->id,
        ]),
      ]);
    }
  }

  /**
   * Notify when patient is admitted
   */
  public function notifyAdmissionCreated(ServiceRequest $request, Admission $admission): void {
    $request->loadMissing('user');
    $message = "Patient has been admitted for request {$request->reference_number}. Expected stay: {$admission->expected_days} days.";

    // Notify user
    if ($request->user) {
      $this->storeNotification(AppUser::class, $request->user_id, 'admission_created', $message, $request);
      if ($request->user->firebase_token) {
        $this->messaging->send(
          [$request->user->firebase_token],
          'Patient Admitted',
          $message,
          ['service_request_id' => $request->id, 'admission_id' => $admission->id]
        );
      }
    }

    // Notify specialist
    $specialist = Doctor::find($admission->specialist_doctor_id);
    if ($specialist && $specialist->firebase_token) {
      $this->messaging->send(
        [$specialist->firebase_token],
        'Patient Admitted',
        "Patient has been admitted for request {$request->reference_number}.",
        ['service_request_id' => $request->id, 'admission_id' => $admission->id]
      );
    }
  }

  /**
   * Notify daily visit
   */
  public function notifyDailyVisit(ServiceRequest $request, Admission $admission): void {
    $request->loadMissing('user');
    $message = "Daily visit completed for request {$request->reference_number}.";

    // Notify user
    if ($request->user) {
      $this->storeNotification(AppUser::class, $request->user_id, 'daily_visit', $message, $request);
      if ($request->user->firebase_token) {
        $this->messaging->send(
          [$request->user->firebase_token],
          'Daily Visit Update',
          $message,
          ['service_request_id' => $request->id, 'admission_id' => $admission->id]
        );
      }
    }
  }

  /**
   * Notify discharge ready
   */
  public function notifyDischargeReady(ServiceRequest $request, Admission $admission): void {
    $request->loadMissing('user');
    $message = "Patient is ready for discharge from request {$request->reference_number}. Please clear all bills.";

    // Notify user
    if ($request->user) {
      $this->storeNotification(AppUser::class, $request->user_id, 'discharge_ready', $message, $request);
      if ($request->user->firebase_token) {
        $this->messaging->send(
          [$request->user->firebase_token],
          'Discharge Ready',
          $message,
          ['service_request_id' => $request->id, 'admission_id' => $admission->id]
        );
      }
    }
  }

  /**
   * Notify status update
   */
  public function notifyStatusUpdate(ServiceRequest $request, string $status): void {
    $request->loadMissing('user');
    $statusLabels = [
      'pending' => 'Pending',
      'payment_pending' => 'Payment Pending',
      'payment_confirmed' => 'Payment Confirmed',
      'nurse_assigned' => 'Nurse Assigned',
      'vitals_uploaded' => 'Vitals Uploaded',
      'gp_assigned' => 'GP Assigned',
      'gp_consultation' => 'GP Consultation',
      'specialist_referred' => 'Specialist Referred',
      'specialist_assigned' => 'Specialist Assigned',
      'admitted' => 'Admitted',
      'discharge_pending' => 'Discharge Pending',
      'discharged' => 'Discharged',
      'completed' => 'Completed',
      'terminated' => 'Terminated',
    ];

    $statusLabel = $statusLabels[$status] ?? ucfirst($status);
    $message = "Request {$request->reference_number} status updated to {$statusLabel}.";

    // Notify user
    if ($request->user) {
      $this->storeNotification(AppUser::class, $request->user_id, 'status_update', $message, $request);
      if ($request->user->firebase_token) {
        $this->messaging->send(
          [$request->user->firebase_token],
          'Status Update',
          $message,
          ['service_request_id' => $request->id, 'status' => $status]
        );
      }
    }
  }

  protected function storeNotification(string $notifiableType, ?int $notifiableId, string $type, string $message, ServiceRequest $request): void {
    if (!$notifiableId) {
      return;
    }

    Notification::create([
      'id' => (string) Str::uuid(),
      'type' => 'service_request.' . $type,
      'notifiable_type' => $notifiableType,
      'notifiable_id' => $notifiableId,
      'data' => json_encode([
        'message' => $message,
        'service_request_id' => $request->id,
        'reference_number' => $request->reference_number,
        'status' => $request->status,
      ]),
    ]);
  }
}




