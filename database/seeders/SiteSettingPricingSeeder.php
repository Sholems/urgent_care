<?php

namespace Database\Seeders;

use App\Models\ConsultationOption;
use App\Models\MealOption;
use App\Models\ServiceRequestAddonOption;
use App\Models\SiteSetting;
use App\Models\TransportOption;
use Illuminate\Database\Seeder;

class SiteSettingPricingSeeder extends Seeder
{
    /**
     * Seed default transport and meals pricing.
     */
    public function run(): void
    {
        $transportDefaults = [
            'ambulance' => 500,
            'private_car' => 250,
            'taxi' => 180,
            'keke' => 120,
            'motor_bike' => 90,
        ];
        $mealDefaults = [
            [
                'value' => 'day_1',
                'label' => '1 Day Meals',
                'days' => 1,
                'amount' => 200,
            ],
            [
                'value' => 'day_3',
                'label' => '3 Days Meals',
                'days' => 3,
                'amount' => 580,
            ],
            [
                'value' => 'day_5',
                'label' => '5 Days Meals',
                'days' => 5,
                'amount' => 950,
            ],
            [
                'value' => 'day_7',
                'label' => '7 Days Meals',
                'days' => 7,
                'amount' => 1300,
            ],
        ];

        $siteSetting = SiteSetting::first() ?? new SiteSetting();

        $existingTransport = (array) ($siteSetting->transport_pricing ?? []);
        $mergedTransport = array_merge($transportDefaults, $existingTransport);
        $existingMealsRaw = (array) ($siteSetting->meal_pricing ?? []);
        $existingMealsByValue = [];

        if (array_is_list($existingMealsRaw)) {
            foreach ($existingMealsRaw as $option) {
                if (!is_array($option) || empty($option['value'])) {
                    continue;
                }
                $value = (string) $option['value'];
                $existingMealsByValue[$value] = [
                    'value' => $value,
                    'label' => isset($option['label']) ? (string) $option['label'] : $value,
                    'days' => isset($option['days']) ? (int) $option['days'] : 0,
                    'amount' => isset($option['amount']) ? (float) $option['amount'] : 0,
                ];
            }
        } else {
            // Backward compatibility for old key => amount shape.
            foreach ($existingMealsRaw as $value => $amount) {
                if (!is_numeric($amount)) {
                    continue;
                }
                preg_match('/\d+/', (string) $value, $matches);
                $days = isset($matches[0]) ? (int) $matches[0] : 0;
                $existingMealsByValue[(string) $value] = [
                    'value' => (string) $value,
                    'label' => $days > 0 ? "{$days} Day Meals" : (string) $value,
                    'days' => $days,
                    'amount' => (float) $amount,
                ];
            }
        }

        foreach ($mealDefaults as $defaultMealOption) {
            $value = (string) $defaultMealOption['value'];
            if (!isset($existingMealsByValue[$value])) {
                $existingMealsByValue[$value] = $defaultMealOption;
            }
        }

        $siteSetting->transport_pricing = $mergedTransport;
        $siteSetting->meal_pricing = array_values($existingMealsByValue);
        $siteSetting->meals_per_day = $siteSetting->meals_per_day ?? 200;
        $siteSetting->save();

        foreach ($transportDefaults as $code => $amount) {
            TransportOption::updateOrCreate(
                ['code' => (string) $code],
                [
                    'label' => ucwords(str_replace('_', ' ', (string) $code)),
                    'amount' => (float) $amount,
                ]
            );
        }

        foreach ($mealDefaults as $option) {
            $value = (string) ($option['value'] ?? '');
            if ($value === '') {
                continue;
            }

            MealOption::updateOrCreate(
                ['code' => $value],
                [
                    'label' => (string) ($option['label'] ?? $value),
                    'days' => (int) ($option['days'] ?? 0),
                    'amount' => (float) ($option['amount'] ?? 0),
                ]
            );
        }

        $consultationLabels = [
            'home_visit' => 'Home Visit',
            'in_clinic' => 'In Clinic',
            'subsequent_in_clinic' => 'Subsequent In Clinic',
        ];
        foreach ((array) config('gp_consultation', []) as $code => $amount) {
            if (!is_numeric($amount)) {
                continue;
            }
            $codeStr = (string) $code;
            ConsultationOption::updateOrCreate(
                ['code' => $codeStr],
                [
                    'label' => $consultationLabels[$codeStr] ?? ucwords(str_replace('_', ' ', $codeStr)),
                    'amount' => (float) $amount,
                ]
            );
        }

        $addonDefaults = [
            'observation' => [
                'label' => 'Observation',
                'amount' => (float) config('cart_pricing.service_request.observation', 0),
            ],
            'specialist' => [
                'label' => 'Specialist consultation',
                'amount' => (float) config('cart_pricing.service_request.specialist', 0),
            ],
        ];
        foreach ($addonDefaults as $code => $data) {
            ServiceRequestAddonOption::updateOrCreate(
                ['code' => $code],
                [
                    'label' => (string) $data['label'],
                    'amount' => (float) $data['amount'],
                ]
            );
        }
    }
}
