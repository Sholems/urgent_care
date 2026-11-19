<?php

namespace App\Orchid\Screens\Monime;

use App\Models\MonimeCredential;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class MonimeCredentialEditScreen extends Screen
{
    public $credential;

    public function query(MonimeCredential $credential): iterable
    {
        return [
            'credential' => $credential,
        ];
    }

    public function name(): ?string
    {
        return $this->credential->exists ? 'Edit Monime Credential' : 'Create Monime Credential';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Create')
                ->icon('pencil')
                ->method('create')
                ->canSee(!$this->credential->exists),

            Button::make('Update')
                ->icon('note')
                ->method('update')
                ->canSee($this->credential->exists),

            Button::make('Remove')
                ->icon('trash')
                ->method('remove')
                ->canSee($this->credential->exists)
                ->confirm('Are you sure you want to delete this credential?'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('credential.name')
                    ->title('Credential Name')
                    ->placeholder('Primary Monime')
                    ->required(),

                Input::make('credential.space_id')
                    ->title('Monime Space ID')
                    ->placeholder('spc-xxxxxxxxxx')
                    ->required(),

                Input::make('credential.api_token')
                    ->title('Monime API Token')
                    ->placeholder('mon_test_xxx or mon_xxx')
                    ->type('password')
                    ->required(!$this->credential->exists)
                    ->help('Leave blank on update to keep the current token.'),

                Input::make('credential.webhook_secret')
                    ->title('Webhook Secret')
                    ->type('password')
                    ->help('Optional. Leave blank on update to keep current secret.'),

                CheckBox::make('credential.enabled')
                    ->title('Enabled')
                    ->sendTrueOrFalse()
                    ->placeholder('Enable Monime integration for API usage'),
            ]),
        ];
    }

    public function create(Request $request)
    {
        return $this->persist($request, false);
    }

    public function update(Request $request)
    {
        return $this->persist($request, true);
    }

    private function persist(Request $request, bool $isUpdate)
    {
        $rules = [
            'credential.name' => 'required|string|max:255',
            'credential.space_id' => 'required|string|max:255',
            'credential.enabled' => 'nullable|boolean',
        ];

        if ($isUpdate) {
            $rules['credential.api_token'] = 'nullable|string|max:4096';
            $rules['credential.webhook_secret'] = 'nullable|string|max:4096';
        } else {
            $rules['credential.api_token'] = 'required|string|max:4096';
            $rules['credential.webhook_secret'] = 'nullable|string|max:4096';
        }

        $data = $request->validate($rules);
        $payload = $data['credential'];
        // Always pin Monime version from code, not admin input.
        $payload['api_version'] = 'caph.2025-08-23';

        if ($isUpdate && empty($payload['api_token'])) {
            unset($payload['api_token']);
        }
        if ($isUpdate && empty($payload['webhook_secret'])) {
            unset($payload['webhook_secret']);
        }

        $this->credential->fill($payload)->save();

        Alert::info($isUpdate
            ? 'You have successfully updated the Monime credential.'
            : 'You have successfully created the Monime credential.');

        return redirect()->route('platform.monime.list');
    }

    public function remove(MonimeCredential $credential)
    {
        $credential->delete();
        Alert::info('You have successfully deleted the Monime credential.');

        return redirect()->route('platform.monime.list');
    }
}
