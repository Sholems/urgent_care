<?php

namespace App\Orchid\Screens\Smtp;

use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Alert;
use Illuminate\Http\Request;
use App\Models\SmtpSetting;

class SmtpSettingScreen extends Screen
{
    public $smtp;

    public function query(): iterable
    {
        return [
            'smtp' => SmtpSetting::first() ?? new SmtpSetting(),
        ];
    }

    public function name(): ?string
    {
        return 'SMTP Settings';
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
                Input::make('smtp.mailer')
                    ->title('Mailer')
                    ->required(),
                Input::make('smtp.host')
                    ->title('Host'),
                Input::make('smtp.port')
                    ->title('Port')
                    ->type('number'),
                Input::make('smtp.username')
                    ->title('Username'),
                Input::make('smtp.password')
                    ->title('Password')
                    ->type('password'),
                Input::make('smtp.encryption')
                    ->title('Encryption'),
                Input::make('smtp.from_address')
                    ->title('From Address'),
                Input::make('smtp.from_name')
                    ->title('From Name'),
            ])
        ];
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'smtp.mailer' => 'required|string',
            'smtp.host' => 'nullable|string',
            'smtp.port' => 'nullable|integer',
            'smtp.username' => 'nullable|string',
            'smtp.password' => 'nullable|string',
            'smtp.encryption' => 'nullable|string',
            'smtp.from_address' => 'nullable|email',
            'smtp.from_name' => 'nullable|string',
        ]);

        $smtp = SmtpSetting::first() ?? new SmtpSetting();
        $smtp->fill($data['smtp']);
        $smtp->save();

        Alert::info('SMTP settings saved successfully.');
        return redirect()->route('platform.smtp.edit');
    }
}

