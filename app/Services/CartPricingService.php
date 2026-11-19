<?php

namespace App\Services;

use App\Models\ConsultationOption;
use App\Models\Doctor;
use App\Models\MealOption;
use App\Models\ServiceRequestAddonOption;
use App\Models\SiteSetting;
use App\Models\TransportOption;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class CartPricingService {
  public function calculate(string $itemType, array $payload): array {
    if ($itemType === 'appointment') {
      return $this->calculateAppointment($payload);
    }

    if ($itemType === 'service_request') {
      return $this->calculateServiceRequest($payload);
    }

    throw ValidationException::withMessages([
      'item_type' => ['Unsupported item type.'],
    ]);
  }

  private function calculateAppointment(array $payload): array {
    $doctorId = (int) ($payload['doctor_id'] ?? 0);
    $doctor = Doctor::find($doctorId);
    if (!$doctor) {
      throw ValidationException::withMessages([
        'payload.doctor_id' => ['Selected doctor does not exist.'],
      ]);
    }

    $doctorPrice = (float) ($doctor->price ?? 0);
    $lineTotal = max($doctorPrice, 0);

    return [
      'line_total' => $lineTotal,
      'pricing_breakdown' => [
        [
          'code' => 'appointment_fee',
          'label' => 'Appointment consultation fee',
          'amount' => $lineTotal,
        ],
      ],
      'payload' => array_merge($payload, [
        'doctor_name' => $doctor->name,
      ]),
    ];
  }

  private function calculateServiceRequest(array $payload): array {
    $transportMode = (string) ($payload['transport_mode'] ?? 'ambulance');
    $consultationType = (string) ($payload['consultation_type'] ?? 'home_visit');
    $includeObservation = (bool) ($payload['include_observation'] ?? false);
    $includeSpecialist = (bool) ($payload['include_specialist'] ?? false);
    $mealDays = max((int) ($payload['meal_days'] ?? 0), 0);
    $mealOption = isset($payload['meal_option']) ? (string) $payload['meal_option'] : null;

    $siteSetting = SiteSetting::first();
    $transportPricing = $this->getTransportPricing($siteSetting);
    $consultationPricing = $this->getConsultationPricingMap();
    $observationResolved = $this->resolveAddonOption('observation');
    $specialistResolved = $this->resolveAddonOption('specialist');
    $observationFee = $observationResolved['amount'];
    $specialistFee = $specialistResolved['amount'];
    $mealPerDay = $siteSetting?->meals_per_day !== null
      ? (float) $siteSetting->meals_per_day
      : (float) config('cart_pricing.service_request.meals_per_day', 0);
    $mealPricing = $this->getMealPricingOptions($siteSetting);

    $transportAmount = (float) ($transportPricing[$transportMode] ?? 0);
    $consultationAmount = (float) ($consultationPricing[$consultationType] ?? 0);

    if (!$consultationAmount) {
      throw ValidationException::withMessages([
        'payload.consultation_type' => ['Invalid consultation type selected.'],
      ]);
    }

    $breakdown = [
      [
        'code' => 'consultation',
        'label' => 'Consultation',
        'amount' => $consultationAmount,
      ],
      [
        'code' => 'transport',
        'label' => 'Transport',
        'amount' => $transportAmount,
      ],
    ];

    $lineTotal = $consultationAmount + $transportAmount;

    if ($includeObservation) {
      $breakdown[] = [
        'code' => 'observation',
        'label' => $observationResolved['label'],
        'amount' => $observationFee,
      ];
      $lineTotal += $observationFee;
    }

    if ($includeSpecialist) {
      $breakdown[] = [
        'code' => 'specialist',
        'label' => $specialistResolved['label'],
        'amount' => $specialistFee,
      ];
      $lineTotal += $specialistFee;
    }

    if ($mealOption && !isset($mealPricing[$mealOption])) {
      throw ValidationException::withMessages([
        'payload.meal_option' => ['Invalid meal option selected.'],
      ]);
    }

    if ($mealOption && isset($mealPricing[$mealOption])) {
      $option = $mealPricing[$mealOption];
      $mealsAmount = (float) ($option['amount'] ?? 0);
      $optionDays = (int) ($option['days'] ?? 0);
      $breakdown[] = [
        'code' => 'meals',
        'label' => $optionDays > 0
          ? "Meals ({$optionDays} day(s))"
          : 'Meals',
        'amount' => $mealsAmount,
      ];
      $lineTotal += $mealsAmount;
      $mealDays = max($optionDays, $mealDays);
    } elseif ($mealDays > 0) {
      $mealsAmount = $mealDays * $mealPerDay;
      $breakdown[] = [
        'code' => 'meals',
        'label' => "Meals ({$mealDays} day(s))",
        'amount' => $mealsAmount,
      ];
      $lineTotal += $mealsAmount;
    }

    return [
      'line_total' => $lineTotal,
      'pricing_breakdown' => $breakdown,
      'payload' => array_merge($payload, [
        'meal_days' => $mealDays,
        'meal_option' => $mealOption,
      ]),
    ];
  }

  private function getMealPricingOptions(?SiteSetting $siteSetting): array {
    $configuredOptions = MealOption::query()
      ->orderBy('id')
      ->get(['code', 'label', 'days', 'amount']);

    if ($configuredOptions->isNotEmpty()) {
      $options = [];
      foreach ($configuredOptions as $option) {
        $options[(string) $option->code] = [
          'value' => (string) $option->code,
          'label' => (string) $option->label,
          'days' => (int) $option->days,
          'amount' => (float) $option->amount,
        ];
      }

      return $options;
    }

    $rawOptions = (array) ($siteSetting?->meal_pricing ?? []);
    $normalized = [];

    if (array_is_list($rawOptions)) {
      foreach ($rawOptions as $option) {
        if (!is_array($option)) {
          continue;
        }
        $value = isset($option['value']) ? (string) $option['value'] : '';
        $amount = isset($option['amount']) ? (float) $option['amount'] : null;
        $days = isset($option['days']) ? (int) $option['days'] : 0;
        $label = isset($option['label']) ? (string) $option['label'] : '';
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

    return $normalized;
  }

  private function getTransportPricing(?SiteSetting $siteSetting): array {
    $configuredOptions = TransportOption::query()
      ->orderBy('id')
      ->get(['code', 'amount']);

    if ($configuredOptions->isNotEmpty()) {
      $pricing = [];
      foreach ($configuredOptions as $option) {
        $pricing[(string) $option->code] = (float) $option->amount;
      }

      return $pricing;
    }

    $defaultTransportPricing = (array) config('cart_pricing.service_request.transport', []);
    $customTransportPricing = (array) ($siteSetting?->transport_pricing ?? []);

    return array_merge($defaultTransportPricing, $customTransportPricing);
  }

  /**
   * Consultation type code => amount (DB rows first, else config gp_consultation).
   *
   * @return array<string, float>
   */
  private function getConsultationPricingMap(): array {
    $configured = ConsultationOption::query()
      ->orderBy('id')
      ->get(['code', 'amount']);

    if ($configured->isNotEmpty()) {
      $map = [];
      foreach ($configured as $option) {
        $map[(string) $option->code] = (float) $option->amount;
      }

      return $map;
    }

    $raw = (array) config('gp_consultation', []);
    $map = [];
    foreach ($raw as $code => $amount) {
      if (is_numeric($amount)) {
        $map[(string) $code] = (float) $amount;
      }
    }

    return $map;
  }

  /**
   * @return array{amount: float, label: string}
   */
  private function resolveAddonOption(string $code): array {
    $row = ServiceRequestAddonOption::query()->where('code', $code)->first();
    if ($row) {
      return [
        'amount' => (float) $row->amount,
        'label' => (string) $row->label,
      ];
    }

    $fallbackLabels = [
      'observation' => 'Observation',
      'specialist' => 'Specialist consultation',
    ];

    $amount = match ($code) {
      'observation' => (float) config('cart_pricing.service_request.observation', 0),
      'specialist' => (float) config('cart_pricing.service_request.specialist', 0),
      default => 0.0,
    };

    return [
      'amount' => $amount,
      'label' => $fallbackLabels[$code] ?? Str::headline(str_replace('_', ' ', $code)),
    ];
  }

  /**
   * Options for GET /service-requests/pricing-options.
   *
   * @return list<array{value: string, label: string, amount: float}>
   */
  public function consultationOptionsForPricingApi(): array {
    $rows = ConsultationOption::query()
      ->orderBy('id')
      ->get(['code', 'label', 'amount']);

    if ($rows->isNotEmpty()) {
      return $rows->map(fn ($option) => [
        'value' => (string) $option->code,
        'label' => (string) $option->label,
        'amount' => (float) $option->amount,
      ])->values()->all();
    }

    $labels = [
      'home_visit' => 'Home Visit',
      'in_clinic' => 'In Clinic',
      'subsequent_in_clinic' => 'Subsequent In Clinic',
    ];

    $out = [];
    foreach ((array) config('gp_consultation', []) as $code => $amount) {
      if (!is_numeric($amount)) {
        continue;
      }
      $codeStr = (string) $code;
      $out[] = [
        'value' => $codeStr,
        'label' => $labels[$codeStr] ?? Str::headline(str_replace('_', ' ', $codeStr)),
        'amount' => (float) $amount,
      ];
    }

    return $out;
  }

  /**
   * @return list<array{value: string, label: string, amount: float}>
   */
  public function addonOptionsForPricingApi(): array {
    $out = [];
    foreach (['observation', 'specialist'] as $code) {
      $resolved = $this->resolveAddonOption($code);
      $out[] = [
        'value' => $code,
        'label' => $resolved['label'],
        'amount' => $resolved['amount'],
      ];
    }

    return $out;
  }

  /**
   * Distinct consultation amounts allowed for request payments.
   *
   * @return list<float>
   */
  public function allowedConsultationPaymentAmounts(): array {
    $options = $this->consultationOptionsForPricingApi();
    $amounts = array_map(fn (array $o) => round((float) $o['amount'], 2), $options);

    return array_values(array_unique($amounts));
  }

  public function addonFeeAmount(string $code): float {
    return $this->resolveAddonOption($code)['amount'];
  }
}
