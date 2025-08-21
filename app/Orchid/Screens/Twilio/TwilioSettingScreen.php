<?php

namespace App\Orchid\Screens\Twilio;

use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Alert;
use Illuminate\Http\Request;
use App\Models\TwilioSetting;

class TwilioSettingScreen extends Screen
{
    public $twilio;

    public function query(): iterable
    {
        return [
            'twilio' => TwilioSetting::first() ?? new TwilioSetting(),
        ];
    }

    public function name(): ?string
    {
        return 'Twilio Settings';
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
                Input::make('twilio.account_sid')
                    ->title('Account SID')
                    ->required(),
                Input::make('twilio.auth_token')
                    ->title('Auth Token')
                    ->type('password')
                    ->required(),
                Input::make('twilio.verify_sid')
                    ->title('Verify Service SID')
                    ->required(),
            ]),
        ];
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'twilio.account_sid' => 'required|string',
            'twilio.auth_token' => 'required|string',
            'twilio.verify_sid' => 'required|string',
        ]);

        $twilio = TwilioSetting::first() ?? new TwilioSetting();
        $twilio->fill($data['twilio']);
        $twilio->save();

        Alert::info('Twilio settings saved successfully.');
        return redirect()->route('platform.twilio.edit');
    }
}
