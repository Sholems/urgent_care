<?php

namespace App\Orchid\Screens\SiteSetting;

use App\Models\SiteSetting;
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
        return [
            'siteSetting' => SiteSetting::first() ?? new SiteSetting(),
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
                    ->placeholder('Enter footer phone number'),
                Input::make('siteSetting.footer_email')
                    ->title('Footer Email')
                    ->type('email')
                    ->placeholder('Enter footer email address'),
                Input::make('siteSetting.contact_phone')
                    ->title('Contact Page Phone')
                    ->placeholder('Enter contact page phone number'),
                Input::make('siteSetting.contact_email')
                    ->title('Contact Page Email')
                    ->type('email')
                    ->placeholder('Enter contact page email address'),
                Input::make('siteSetting.opening_hours.mon_tue')
                    ->title('Opening Hours (Mon - Tue)')
                    ->placeholder('e.g. 8:00 - 17:30'),
                Input::make('siteSetting.opening_hours.wed_thu')
                    ->title('Opening Hours (Wed - Thu)')
                    ->placeholder('e.g. 10:45 - 15:00'),
                Input::make('siteSetting.opening_hours.fri')
                    ->title('Opening Hours (Fri)')
                    ->placeholder('e.g. 8:00 - 17:30'),
                Input::make('siteSetting.opening_hours.sat')
                    ->title('Opening Hours (Sat)')
                    ->placeholder('e.g. 10:45 - 15:00'),
                Input::make('siteSetting.opening_hours.sun')
                    ->title('Opening Hours (Sun)')
                    ->placeholder('e.g. Closed'),
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
            'siteSetting.opening_hours.mon_tue' => 'nullable|string|max:255',
            'siteSetting.opening_hours.wed_thu' => 'nullable|string|max:255',
            'siteSetting.opening_hours.fri' => 'nullable|string|max:255',
            'siteSetting.opening_hours.sat' => 'nullable|string|max:255',
            'siteSetting.opening_hours.sun' => 'nullable|string|max:255',
            'siteSetting.header_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'siteSetting.footer_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $siteSetting = SiteSetting::first() ?? new SiteSetting();

        $siteSetting->footer_phone = $request->input('siteSetting.footer_phone');
        $siteSetting->footer_email = $request->input('siteSetting.footer_email');
        $siteSetting->contact_phone = $request->input('siteSetting.contact_phone');
        $siteSetting->contact_email = $request->input('siteSetting.contact_email');

        $openingHours = [
            'mon_tue' => $request->input('siteSetting.opening_hours.mon_tue'),
            'wed_thu' => $request->input('siteSetting.opening_hours.wed_thu'),
            'fri' => $request->input('siteSetting.opening_hours.fri'),
            'sat' => $request->input('siteSetting.opening_hours.sat'),
            'sun' => $request->input('siteSetting.opening_hours.sun'),
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
