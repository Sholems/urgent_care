<?php

namespace App\Orchid\Screens\Pricing;

use App\Models\MealOption;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class MealOptionEditScreen extends Screen
{
    public $mealOption;

    public function query(MealOption $mealOption): iterable
    {
        return [
            'mealOption' => $mealOption,
        ];
    }

    public function name(): ?string
    {
        return $this->mealOption->exists
            ? 'Edit Meal Option'
            : 'Create Meal Option';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Create')
                ->icon('bs.plus-circle')
                ->method('createOrUpdate')
                ->canSee(!$this->mealOption->exists),

            Button::make('Update')
                ->icon('bs.check-circle')
                ->method('createOrUpdate')
                ->canSee($this->mealOption->exists),

            Button::make('Delete')
                ->icon('bs.trash3')
                ->method('remove')
                ->canSee($this->mealOption->exists)
                ->confirm('Are you sure you want to delete this meal option?'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('mealOption.label')
                    ->required()
                    ->title('Label')
                    ->placeholder('e.g. 3 Days Meals'),

                Input::make('mealOption.code')
                    ->title('Code (optional)')
                    ->placeholder('e.g. day_3')
                    ->help('If empty, code will be auto-generated from label.'),

                Input::make('mealOption.days')
                    ->required()
                    ->type('number')
                    ->step('1')
                    ->min(0)
                    ->title('Days'),

                Input::make('mealOption.amount')
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
            'mealOption.label' => 'required|string|max:255',
            'mealOption.code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('meal_options', 'code')->ignore($this->mealOption->id),
            ],
            'mealOption.days' => 'required|integer|min:0',
            'mealOption.amount' => 'required|numeric|min:0',
        ]);

        $data = $validated['mealOption'];
        $code = trim((string) ($data['code'] ?? ''));
        if ($code === '') {
            $code = Str::slug((string) $data['label'], '_');
        } else {
            $code = Str::slug($code, '_');
        }

        $this->mealOption->fill([
            'label' => (string) $data['label'],
            'code' => $code,
            'days' => (int) $data['days'],
            'amount' => (float) $data['amount'],
        ])->save();

        Alert::info('Meal option saved successfully.');

        return redirect()->route('platform.meal-options.list');
    }

    public function remove(MealOption $mealOption)
    {
        $mealOption->delete();
        Alert::info('Meal option deleted successfully.');

        return redirect()->route('platform.meal-options.list');
    }
}
