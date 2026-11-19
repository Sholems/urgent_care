<?php

namespace App\Orchid\Screens\SiteSetting;

use App\Models\SiteSetting;
use App\Support\ContactPhoneFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class SiteSettingScreen extends Screen
{
    public $siteSetting;

    public function query(): iterable
    {
        $siteSetting = SiteSetting::first() ?? new SiteSetting();

        if (is_array($siteSetting->opening_hours)) {
            $openingHours = $siteSetting->opening_hours;

            if (!array_key_exists('mon_thu', $openingHours)) {
                $legacyValues = array_filter([
                    $openingHours['mon_tue'] ?? null,
                    $openingHours['wed_thu'] ?? null,
                ]);

                if (!empty($legacyValues)) {
                    $openingHours['mon_thu'] = reset($legacyValues);
                }
            }

            unset($openingHours['mon_tue'], $openingHours['wed_thu']);

            if (
                !array_key_exists('mon_sat', $openingHours)
                || $openingHours['mon_sat'] === null
                || $openingHours['mon_sat'] === ''
            ) {
                $openingHours['mon_sat'] = $openingHours['mon_thu']
                    ?? $openingHours['fri']
                    ?? $openingHours['sat']
                    ?? null;
            }
            if (
                !array_key_exists('sun_holidays', $openingHours)
                || $openingHours['sun_holidays'] === null
                || $openingHours['sun_holidays'] === ''
            ) {
                $openingHours['sun_holidays'] = $openingHours['sun'] ?? null;
            }

            $siteSetting->opening_hours = $openingHours;
        }

        return [
            'siteSetting' => $siteSetting,
        ];
    }

    public function name(): ?string
    {
        return 'Site Settings';
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
                Input::make('siteSetting.footer_phone')
                    ->title('Footer Phone')
                    ->placeholder('e.g. +232 72 735 530 / +232 78 100 017 / +232 30 439 975')
                    ->help('Saved to both footer and contact areas (same number shown site-wide).'),
                Input::make('siteSetting.footer_email')
                    ->title('Footer Email')
                    ->type('email')
                    ->placeholder('Enter footer email address'),
                Input::make('siteSetting.contact_phone')
                    ->title('Contact Page Phone')
                    ->placeholder('e.g. +232 72 735 530 / +232 78 100 017 / +232 30 439 975')
                    ->help('Takes priority over footer phone when both are filled; both fields are kept in sync on save.'),
                Input::make('siteSetting.contact_email')
                    ->title('Contact Page Email')
                    ->type('email')
                    ->placeholder('Enter contact page email address'),
                Input::make('siteSetting.opening_hours.mon_sat')
                    ->title('Opening Hours (Mon - Sat)')
                    ->placeholder('e.g. 8:00 am to 04:00 pm'),
                Input::make('siteSetting.opening_hours.sun_holidays')
                    ->title('Opening Hours (Sun & Holidays)')
                    ->placeholder('e.g. Appointment/Emergency'),
                Input::make('siteSetting.header_logo')
                    ->title('Header Logo')
                    ->type('file'),
                Input::make('siteSetting.footer_logo')
                    ->title('Footer Logo')
                    ->type('file'),
            ]),
        ];
    }

    public function save(Request $request)
    {
        $request->validate([
            'siteSetting.footer_phone' => 'nullable|string|max:255',
            'siteSetting.footer_email' => 'nullable|email|max:255',
            'siteSetting.contact_phone' => 'nullable|string|max:255',
            'siteSetting.contact_email' => 'nullable|email|max:255',
            'siteSetting.opening_hours.mon_sat' => 'nullable|string|max:255',
            'siteSetting.opening_hours.sun_holidays' => 'nullable|string|max:255',
            'siteSetting.header_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'siteSetting.footer_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $siteSetting = SiteSetting::first() ?? new SiteSetting();

        $resolvedPhone = ContactPhoneFormatter::resolveFromInput(
            $request->input('siteSetting.contact_phone'),
            $request->input('siteSetting.footer_phone')
        ) ?? ContactPhoneFormatter::format(config('site.default_phone'));

        $siteSetting->contact_phone = $resolvedPhone;
        $siteSetting->footer_phone = $resolvedPhone;
        $siteSetting->footer_email = $request->input('siteSetting.footer_email');
        $siteSetting->contact_email = $request->input('siteSetting.contact_email');

        $openingHours = [
            'mon_sat' => $request->input('siteSetting.opening_hours.mon_sat'),
            'sun_holidays' => $request->input('siteSetting.opening_hours.sun_holidays'),
        ];

        $openingHours = array_filter($openingHours, fn ($value) => $value !== null && $value !== '');
        $siteSetting->opening_hours = empty($openingHours) ? null : $openingHours;

        if ($request->hasFile('siteSetting.header_logo')) {
            $this->deleteImage($siteSetting->header_logo ?? null);
            $path = $request->file('siteSetting.header_logo')->store('', 'public');
            $siteSetting->header_logo = 'storage/' . $path;
        }

        if ($request->hasFile('siteSetting.footer_logo')) {
            $this->deleteImage($siteSetting->footer_logo ?? null);
            $path = $request->file('siteSetting.footer_logo')->store('', 'public');
            $siteSetting->footer_logo = 'storage/' . $path;
        }

        $siteSetting->save();

        Alert::info('Site settings saved successfully.');

        return redirect()->route('platform.site.settings');
    }

    protected function deleteImage(?string $path): void
    {
        if (!$path) {
            return;
        }

        $relativePath = $path;

        if (Str::contains($relativePath, 'storage/')) {
            $relativePath = Str::after($relativePath, 'storage/');
        }

        if (!empty($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }
}
