<?php

namespace App\Orchid\Screens\Pricing;

use App\Models\ConsultationOption;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class ConsultationOptionEditScreen extends Screen
{
    public $consultationOption;

    public function query(ConsultationOption $consultationOption): iterable
    {
        return [
            'consultationOption' => $consultationOption,
        ];
    }

    public function name(): ?string
    {
        return $this->consultationOption->exists
            ? 'Edit Consultation Option'
            : 'Create Consultation Option';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Create')
                ->icon('bs.plus-circle')
                ->method('createOrUpdate')
                ->canSee(!$this->consultationOption->exists),

            Button::make('Update')
                ->icon('bs.check-circle')
                ->method('createOrUpdate')
                ->canSee($this->consultationOption->exists),

            Button::make('Delete')
                ->icon('bs.trash3')
                ->method('remove')
                ->canSee($this->consultationOption->exists)
                ->confirm('Are you sure you want to delete this consultation option?'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('consultationOption.label')
                    ->required()
                    ->title('Label')
                    ->placeholder('e.g. Home Visit'),

                Input::make('consultationOption.code')
                    ->title('Code (optional)')
                    ->placeholder('e.g. home_visit')
                    ->help('If empty, code will be auto-generated from label.'),

                Input::make('consultationOption.amount')
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
            'consultationOption.label' => 'required|string|max:255',
            'consultationOption.code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('consultation_options', 'code')->ignore($this->consultationOption->id),
            ],
            'consultationOption.amount' => 'required|numeric|min:0',
        ]);

        $data = $validated['consultationOption'];
        $code = trim((string) ($data['code'] ?? ''));
        if ($code === '') {
            $code = Str::slug((string) $data['label'], '_');
        } else {
            $code = Str::slug($code, '_');
        }

        $this->consultationOption->fill([
            'label' => (string) $data['label'],
            'code' => $code,
            'amount' => (float) $data['amount'],
        ])->save();

        Alert::info('Consultation option saved successfully.');

        return redirect()->route('platform.consultation-options.list');
    }

    public function remove(ConsultationOption $consultationOption)
    {
        $consultationOption->delete();
        Alert::info('Consultation option deleted successfully.');

        return redirect()->route('platform.consultation-options.list');
    }
}
