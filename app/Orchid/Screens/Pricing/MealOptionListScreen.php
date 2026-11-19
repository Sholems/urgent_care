<?php

namespace App\Orchid\Screens\Pricing;

use App\Models\MealOption;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class MealOptionListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'mealOptions' => MealOption::filters()
                ->defaultSort('updated_at', 'desc')
                ->paginate(20),
        ];
    }

    public function name(): ?string
    {
        return 'Meal Options';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Add Meal Option')
                ->icon('bs.plus-circle')
                ->route('platform.meal-options.edit'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('mealOptions', [
                TD::make('label', 'Label')
                    ->sort()
                    ->filter(Input::make())
                    ->render(fn (MealOption $option) => Link::make($option->label)
                        ->route('platform.meal-options.edit', $option->id)),

                TD::make('code', 'Code')
                    ->sort()
                    ->filter(Input::make()),

                TD::make('days', 'Days')
                    ->sort(),

                TD::make('amount', 'Price')
                    ->sort()
                    ->render(fn (MealOption $option) => number_format((float) $option->amount, 2)),

                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (MealOption $option) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(__('Edit'))
                                ->route('platform.meal-options.edit', $option->id)
                                ->icon('bs.pencil'),
                            Button::make('Delete')
                                ->icon('bs.trash3')
                                ->confirm('Are you sure you want to delete this meal option?')
                                ->method('remove')
                                ->parameters([
                                    'mealOption' => $option->id,
                                ]),
                        ])),
            ]),
        ];
    }

    public function remove(MealOption $mealOption)
    {
        $mealOption->delete();
        Alert::info('Meal option deleted successfully.');

        return redirect()->route('platform.meal-options.list');
    }
}
