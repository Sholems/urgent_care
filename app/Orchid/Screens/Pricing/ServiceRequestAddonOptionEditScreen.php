<?php

namespace App\Orchid\Screens\Pricing;

use App\Models\ServiceRequestAddonOption;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class ServiceRequestAddonOptionEditScreen extends Screen
{
    public $serviceRequestAddonOption;

    public function query(ServiceRequestAddonOption $serviceRequestAddonOption): iterable
    {
        return [
            'serviceRequestAddonOption' => $serviceRequestAddonOption,
        ];
    }

    public function name(): ?string
    {
        return $this->serviceRequestAddonOption->exists
            ? 'Edit Service Add-on'
            : 'Create Service Add-on';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Create')
                ->icon('bs.plus-circle')
                ->method('createOrUpdate')
                ->canSee(!$this->serviceRequestAddonOption->exists),

            Button::make('Update')
                ->icon('bs.check-circle')
                ->method('createOrUpdate')
                ->canSee($this->serviceRequestAddonOption->exists),

            Button::make('Delete')
                ->icon('bs.trash3')
                ->method('remove')
                ->canSee($this->serviceRequestAddonOption->exists)
                ->confirm('Are you sure you want to delete this add-on?'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('serviceRequestAddonOption.label')
                    ->required()
                    ->title('Label')
                    ->placeholder('e.g. Observation'),

                Input::make('serviceRequestAddonOption.code')
                    ->title('Code (optional)')
                    ->placeholder('e.g. observation')
                    ->help('Use observation or specialist for cart pricing. If empty, code is generated from label.'),

                Input::make('serviceRequestAddonOption.amount')
                    ->required()
                    ->type('number')
                    ->step('0.01')
                    ->min(0)
                    ->title('Price'),
            ]),
        ];
    }

    public function createOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'serviceRequestAddonOption.label' => 'required|string|max:255',
            'serviceRequestAddonOption.code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('service_request_addon_options', 'code')->ignore($this->serviceRequestAddonOption->id),
            ],
            'serviceRequestAddonOption.amount' => 'required|numeric|min:0',
        ]);

        $data = $validated['serviceRequestAddonOption'];
        $code = trim((string) ($data['code'] ?? ''));
        if ($code === '') {
            $code = Str::slug((string) $data['label'], '_');
        } else {
            $code = Str::slug($code, '_');
        }

        $this->serviceRequestAddonOption->fill([
            'label' => (string) $data['label'],
            'code' => $code,
            'amount' => (float) $data['amount'],
        ])->save();

        Alert::info('Add-on saved successfully.');

        return redirect()->route('platform.service-request-addon-options.list');
    }

    public function remove(ServiceRequestAddonOption $serviceRequestAddonOption)
    {
        $serviceRequestAddonOption->delete();
        Alert::info('Add-on deleted successfully.');

        return redirect()->route('platform.service-request-addon-options.list');
    }
}
