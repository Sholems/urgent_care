<?php

namespace App\Orchid\Screens\Pricing;

use App\Models\TransportOption;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class TransportOptionEditScreen extends Screen
{
    public $transportOption;

    public function query(TransportOption $transportOption): iterable
    {
        return [
            'transportOption' => $transportOption,
        ];
    }

    public function name(): ?string
    {
        return $this->transportOption->exists
            ? 'Edit Transport Option'
            : 'Create Transport Option';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Create')
                ->icon('bs.plus-circle')
                ->method('createOrUpdate')
                ->canSee(!$this->transportOption->exists),

            Button::make('Update')
                ->icon('bs.check-circle')
                ->method('createOrUpdate')
                ->canSee($this->transportOption->exists),

            Button::make('Delete')
                ->icon('bs.trash3')
                ->method('remove')
                ->canSee($this->transportOption->exists)
                ->confirm('Are you sure you want to delete this transport option?'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('transportOption.label')
                    ->required()
                    ->title('Label')
                    ->placeholder('e.g. Keke'),

                Input::make('transportOption.code')
                    ->title('Code (optional)')
                    ->placeholder('e.g. keke')
                    ->help('If empty, code will be auto-generated from label.'),

                Input::make('transportOption.amount')
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
            'transportOption.label' => 'required|string|max:255',
            'transportOption.code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('transport_options', 'code')->ignore($this->transportOption->id),
            ],
            'transportOption.amount' => 'required|numeric|min:0',
        ]);

        $data = $validated['transportOption'];
        $code = trim((string) ($data['code'] ?? ''));
        if ($code === '') {
            $code = Str::slug((string) $data['label'], '_');
        } else {
            $code = Str::slug($code, '_');
        }

        $this->transportOption->fill([
            'label' => (string) $data['label'],
            'code' => $code,
            'amount' => (float) $data['amount'],
        ])->save();

        Alert::info('Transport option saved successfully.');

        return redirect()->route('platform.transport-options.list');
    }

    public function remove(TransportOption $transportOption)
    {
        $transportOption->delete();
        Alert::info('Transport option deleted successfully.');

        return redirect()->route('platform.transport-options.list');
    }
}
