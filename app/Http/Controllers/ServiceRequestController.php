<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\AppUser;
use App\Models\MealOption;
use App\Models\SiteSetting;
use App\Models\TransportOption;
use App\Services\CartPricingService;
use App\Services\ServiceRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceRequestController extends Controller {
  public function __construct(
    private ServiceRequestService $serviceRequestService,
    private CartPricingService $cartPricingService
  ) {
  }

  /**
   * Create a new service request
   */
  public function create(Request $request) {
    $validated = $request->validate([
      'user_id' => 'required|exists:app_users,id',
      'transport_mode' => 'required|string|max:255',
      'patient_name' => 'required|string|max:255',
      'patient_phone' => 'required|string|max:20',
      'patient_address' => 'required|string',
      'patient_gender' => 'required|in:male,female,other',
      'symptoms' => 'nullable|string',
    ]);

    $serviceRequest = $this->serviceRequestService->create($validated);

    return response()->json([
      'message' => 'Service request created successfully',
      'service_request' => $serviceRequest->load('user'),
    ], 201);
  }

  /**
   * Get service request details
   */
  public function show($id) {
    $serviceRequest = ServiceRequest::with([
      'user',
      'payments',
      'vitalsReports.nurse',
      'patientFiles',
      'referrals.gpDoctor',
      'referrals.specialistDoctor',
      'admissions.specialistDoctor',
      'admissions.dailyVisits.doctor',
      'assignments',
    ])->findOrFail($id);

    return response()->json([
      'service_request' => $serviceRequest,
    ]);
  }

  /**
   * Get user's service requests
   */
  public function getUserRequests($userId) {
    $perPage = (int) request()->query('per_page', 15);
    $page = (int) request()->query('page', 1);
    $perPage = max(1, min($perPage, 100));

    $requests = ServiceRequest::where('user_id', $userId)
      ->with(['payments', 'assignments'])
      ->orderByDesc('created_at')
      ->paginate($perPage, ['*'], 'page', $page);

    return $this->paginatedResponse($requests);
  }

  /**
   * Update request status
   */
  public function updateStatus(Request $request, $id) {
    $validated = $request->validate([
      'status' => 'required|in:pending,payment_pending,payment_confirmed,nurse_assigned,vitals_uploaded,gp_assigned,gp_consultation,specialist_referred,specialist_assigned,admitted,discharge_pending,discharged,completed,terminated',
    ]);

    $serviceRequest = $this->serviceRequestService->updateStatus($id, $validated['status']);

    return response()->json([
      'message' => 'Status updated successfully',
      'service_request' => $serviceRequest,
    ]);
  }

  /**
   * Admin view all requests
   */
  public function admin(Request $request) {
    $perPage = (int) $request->query('per_page', 15);
    $page = (int) $request->query('page', 1);
    $status = $request->query('status');
    $perPage = max(1, min($perPage, 100));

    $query = ServiceRequest::with(['user', 'payments', 'assignments']);

    if ($status) {
      $query->where('status', $status);
    }

    $requests = $query->orderByDesc('created_at')
      ->paginate($perPage, ['*'], 'page', $page);

    return $this->paginatedResponse($requests);
  }

  /**
   * Nurse view assigned requests
   */
  public function nurseRequests(Request $request) {
    $nurseId = (int) $request->query('nurse_id');
    
    if (!$nurseId) {
      return response()->json([
        'message' => 'Nurse ID is required',
      ], 400);
    }

    $nurse = \App\Models\Nurse::findOrFail($nurseId);

    $perPage = (int) $request->query('per_page', 15);
    $page = (int) $request->query('page', 1);
    $perPage = max(1, min($perPage, 100));

    $requests = ServiceRequest::whereHas('assignments', function($q) use ($nurse) {
      $q->where('assigned_to_type', 'nurse')
        ->where('assigned_to_id', $nurse->id)
        ->where('status', 'active');
    })
      ->with(['user', 'payments', 'vitalsReports'])
      ->orderByDesc('created_at')
      ->paginate($perPage, ['*'], 'page', $page);

    return $this->paginatedResponse($requests);
  }

  /**
   * Doctor view assigned requests
   */
  public function doctorRequests($doctorId) {
    $perPage = (int) request()->query('per_page', 15);
    $page = (int) request()->query('page', 1);
    $perPage = max(1, min($perPage, 100));

    $requests = ServiceRequest::whereHas('assignments', function($q) use ($doctorId) {
      $q->where(function($q2) use ($doctorId) {
        $q2->where('assigned_to_type', 'gp')
          ->orWhere('assigned_to_type', 'specialist');
      })
        ->where('assigned_to_id', $doctorId)
        ->where('status', 'active');
    })
      ->with(['user', 'payments', 'vitalsReports', 'patientFiles', 'referrals'])
      ->orderByDesc('created_at')
      ->paginate($perPage, ['*'], 'page', $page);

    return $this->paginatedResponse($requests);
  }

  /**
   * Assign nurse to request
   */
  public function assignNurse(Request $request, $id) {
    $validated = $request->validate([
      'nurse_id' => 'required|exists:nurses,id',
    ]);

    $assignment = $this->serviceRequestService->assignNurse($id, $validated['nurse_id']);

    return response()->json([
      'message' => 'Nurse assigned successfully',
      'assignment' => $assignment,
    ]);
  }

  /**
   * Assign GP to request
   */
  public function assignGP(Request $request, $id) {
    $validated = $request->validate([
      'doctor_id' => 'required|exists:doctors,id',
    ]);

    $assignment = $this->serviceRequestService->assignGP($id, $validated['doctor_id']);

    return response()->json([
      'message' => 'GP assigned successfully',
      'assignment' => $assignment,
    ]);
  }

  /**
   * Terminate request
   */
  public function terminate($id) {
    $serviceRequest = $this->serviceRequestService->updateStatus($id, 'terminated');

    return response()->json([
      'message' => 'Request terminated successfully',
      'service_request' => $serviceRequest,
    ]);
  }

  /**
   * Service request pricing options used by mobile screens.
   */
  public function pricingOptions() {
    $siteSetting = SiteSetting::first();
    $transportOptions = TransportOption::query()
      ->orderBy('id')
      ->get(['code', 'label', 'amount']);

    $transportModes = $transportOptions->map(fn ($option) => [
      'value' => (string) $option->code,
      'label' => (string) $option->label,
      'amount' => (float) $option->amount,
    ])->values()->all();

    if (empty($transportModes)) {
      $defaultTransportPricing = (array) config('cart_pricing.service_request.transport', []);
      $customTransportPricing = (array) ($siteSetting?->transport_pricing ?? []);
      $transportPricing = array_merge($defaultTransportPricing, $customTransportPricing);
      $transportModes = [
        [
          'value' => 'ambulance',
          'label' => 'Ambulance',
          'amount' => (float) ($transportPricing['ambulance'] ?? 0),
        ],
        [
          'value' => 'private_car',
          'label' => 'Private Car',
          'amount' => (float) ($transportPricing['private_car'] ?? 0),
        ],
        [
          'value' => 'taxi',
          'label' => 'Taxi',
          'amount' => (float) ($transportPricing['taxi'] ?? 0),
        ],
        [
          'value' => 'keke',
          'label' => 'Keke',
          'amount' => (float) ($transportPricing['keke'] ?? 0),
        ],
        [
          'value' => 'motor_bike',
          'label' => 'Motor Bike',
          'amount' => (float) ($transportPricing['motor_bike'] ?? 0),
        ],
      ];
    }

    $mealsPerDay = $siteSetting?->meals_per_day !== null
      ? (float) $siteSetting->meals_per_day
      : (float) config('cart_pricing.service_request.meals_per_day', 0);
    $mealOptionRows = MealOption::query()
      ->orderBy('id')
      ->get(['code', 'label', 'days', 'amount']);

    $mealOptions = $mealOptionRows->map(fn ($option) => [
      'value' => (string) $option->code,
      'label' => (string) $option->label,
      'days' => (int) $option->days,
      'amount' => (float) $option->amount,
    ])->values()->all();

    if (empty($mealOptions)) {
      $mealOptions = $this->normalizeMealOptions((array) ($siteSetting?->meal_pricing ?? []));
    }

    return response()->json([
      'currency' => (string) config('cart_pricing.currency', 'SLE'),
      'transport_modes' => $transportModes,
      'meal_options' => $mealOptions,
      'meals_per_day' => $mealsPerDay,
      'consultation_options' => $this->cartPricingService->consultationOptionsForPricingApi(),
      'addon_options' => $this->cartPricingService->addonOptionsForPricingApi(),
    ]);
  }

  private function normalizeMealOptions(array $rawOptions): array {
    $normalized = [];

    if (array_is_list($rawOptions)) {
      foreach ($rawOptions as $option) {
        if (!is_array($option)) {
          continue;
        }
        $value = isset($option['value']) ? (string) $option['value'] : '';
        $label = isset($option['label']) ? (string) $option['label'] : '';
        $amount = isset($option['amount']) ? (float) $option['amount'] : null;
        $days = isset($option['days']) ? (int) $option['days'] : 0;
        if ($value === '' || $amount === null) {
          continue;
        }
        $normalized[$value] = [
          'value' => $value,
          'label' => $label !== '' ? $label : Str::headline(str_replace('_', ' ', $value)),
          'days' => max($days, 0),
          'amount' => max($amount, 0),
        ];
      }
    } else {
      // Backward-compatible support for legacy key => amount shape.
      foreach ($rawOptions as $key => $amount) {
        if (!is_numeric($amount)) {
          continue;
        }
        $value = (string) $key;
        preg_match('/\d+/', $value, $matches);
        $days = isset($matches[0]) ? (int) $matches[0] : 0;
        $normalized[$value] = [
          'value' => $value,
          'label' => $days > 0 ? "{$days} Day Meals" : Str::headline(str_replace('_', ' ', $value)),
          'days' => $days,
          'amount' => max((float) $amount, 0),
        ];
      }
    }

    return array_values($normalized);
  }
}




