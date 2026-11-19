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

class TransportPricingScreen extends Screen
{
    public function query(): iterable
    {
        $siteSetting = SiteSetting::first() ?? new SiteSetting();
        $transportPricing = (array) ($siteSetting->transport_pricing ?? []);
        $transportRows = [];

        foreach ($transportPricing as $code => $amount) {
            if (!is_numeric($amount)) {
                continue;
            }
            $transportRows[] = [
                'label' => Str::headline(str_replace('_', ' ', (string) $code)),
                'code' => (string) $code,
                'amount' => (float) $amount,
            ];
        }

        return [
            'siteSetting' => $siteSetting,
            'pricing.transport_options' => $transportRows,
        ];
    }

    public function name(): ?string
    {
        return 'Transport Pricing';
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
                Matrix::make('pricing.transport_options')
                    ->title('Transport Options')
                    ->columns([
                        'Label',
                        'Code (optional)',
                        'Price',
                    ])
                    ->fields([
                        'label' => Input::make()->placeholder('e.g. Keke'),
                        'code' => Input::make()->placeholder('e.g. keke'),
                        'amount' => Input::make()->type('number')->step('0.01')->min(0),
                    ])
                    ->help('Add any transport types and prices you want.'),
            ]),
        ];
    }

    public function save(Request $request)
    {
        $siteSetting = SiteSetting::first() ?? new SiteSetting();
        $rows = (array) $request->input('pricing.transport_options', []);
        $transportPricing = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $label = trim((string) ($row['label'] ?? ''));
            $code = trim((string) ($row['code'] ?? ''));
            $amountRaw = $row['amount'] ?? null;

            if ($label === '' || $amountRaw === null || $amountRaw === '' || !is_numeric($amountRaw)) {
                continue;
            }

            $key = $code !== '' ? Str::slug($code, '_') : Str::slug($label, '_');
            if ($key === '') {
                continue;
            }

            $transportPricing[$key] = max((float) $amountRaw, 0);
        }

        $siteSetting->transport_pricing = empty($transportPricing)
            ? null
            : $transportPricing;
        $siteSetting->save();

        Alert::info('Transport pricing saved successfully.');

        return redirect()->route('platform.transport.pricing');
    }
}
