<?php

namespace App\Orchid\Screens\Pricing;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class MealPricingScreen extends Screen
{
    public function query(): iterable
    {
        $siteSetting = SiteSetting::first() ?? new SiteSetting();
        $rawMealPricing = (array) ($siteSetting->meal_pricing ?? []);
        $mealRows = [];

        if (array_is_list($rawMealPricing)) {
            foreach ($rawMealPricing as $option) {
                if (!is_array($option)) {
                    continue;
                }
                $mealRows[] = [
                    'label' => (string) ($option['label'] ?? ''),
                    'value' => (string) ($option['value'] ?? ''),
                    'days' => (int) ($option['days'] ?? 0),
                    'amount' => (float) ($option['amount'] ?? 0),
                ];
            }
        } else {
            // Legacy support for key => amount shape.
            foreach ($rawMealPricing as $value => $amount) {
                if (!is_numeric($amount)) {
                    continue;
                }
                preg_match('/\d+/', (string) $value, $matches);
                $days = isset($matches[0]) ? (int) $matches[0] : 0;
                $mealRows[] = [
                    'label' => $days > 0 ? "{$days} Day Meals" : Str::headline(str_replace('_', ' ', (string) $value)),
                    'value' => (string) $value,
                    'days' => $days,
                    'amount' => (float) $amount,
                ];
            }
        }

        return [
            'siteSetting' => $siteSetting,
            'pricing.meal_options' => $mealRows,
        ];
    }

    public function name(): ?string
    {
        return 'Meal Options';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Save')
                ->icon('check')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Matrix::make('pricing.meal_options')
                    ->title('Meal Options')
                    ->columns([
                        'Label',
                        'Code (optional)',
                        'Days',
                        'Price',
                    ])
                    ->fields([
                        'label' => Input::make()->placeholder('e.g. 3 Days Meals'),
                        'value' => Input::make()->placeholder('e.g. day_3'),
                        'days' => Input::make()->type('number')->min(0)->step('1'),
                        'amount' => Input::make()->type('number')->step('0.01')->min(0),
                    ])
                    ->help('Add any meal options and prices you want.'),
                Input::make('siteSetting.meals_per_day')
                    ->title('Meals Price Per Day (optional fallback)')
                    ->type('number')
                    ->step('0.01')
                    ->min(0),
            ]),
        ];
    }

    public function save(Request $request)
    {
        $request->validate([
            'siteSetting.meals_per_day' => 'nullable|numeric|min:0',
        ]);

        $siteSetting = SiteSetting::first() ?? new SiteSetting();
        $rows = (array) $request->input('pricing.meal_options', []);
        $normalizedOptions = [];

        foreach ($rows as $option) {
            if (!is_array($option)) {
                continue;
            }
            $label = trim((string) ($option['label'] ?? ''));
            $value = trim((string) ($option['value'] ?? ''));
            $amountRaw = $option['amount'] ?? null;

            if ($label === '' || $amountRaw === null || $amountRaw === '' || !is_numeric($amountRaw)) {
                continue;
            }

            $daysRaw = $option['days'] ?? 0;
            $days = is_numeric($daysRaw) ? max((int) $daysRaw, 0) : 0;
            $slug = $value !== '' ? Str::slug($value, '_') : Str::slug($label, '_');
            if ($slug === '') {
                continue;
            }

            $normalizedOptions[] = [
                'value' => $slug,
                'label' => $label,
                'days' => $days,
                'amount' => max((float) $amountRaw, 0),
            ];
        }

        $siteSetting->meal_pricing = empty($normalizedOptions) ? null : $normalizedOptions;

        $siteSetting->meals_per_day = $request->input('siteSetting.meals_per_day') !== null
            ? (float) $request->input('siteSetting.meals_per_day')
            : null;
        $siteSetting->save();

        Alert::info('Meal options saved successfully.');

        return redirect()->route('platform.meal.pricing');
    }
}
