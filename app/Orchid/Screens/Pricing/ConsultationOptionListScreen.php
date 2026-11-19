<?php

namespace App\Orchid\Screens\Pricing;

use App\Models\ConsultationOption;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class ConsultationOptionListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'consultationOptions' => ConsultationOption::filters()
                ->defaultSort('updated_at', 'desc')
                ->paginate(20),
        ];
    }

    public function name(): ?string
    {
        return 'Consultation Options';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Add Consultation Option')
                ->icon('bs.plus-circle')
                ->route('platform.consultation-options.edit'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('consultationOptions', [
                TD::make('label', 'Label')
                    ->sort()
                    ->filter(Input::make())
                    ->render(fn (ConsultationOption $option) => Link::make($option->label)
                        ->route('platform.consultation-options.edit', $option->id)),

                TD::make('code', 'Code')
                    ->sort()
                    ->filter(Input::make()),

                TD::make('amount', 'Price')
                    ->sort()
                    ->render(fn (ConsultationOption $option) => number_format((float) $option->amount, 2)),

                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (ConsultationOption $option) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(__('Edit'))
                                ->route('platform.consultation-options.edit', $option->id)
                                ->icon('bs.pencil'),
                            Button::make('Delete')
                                ->icon('bs.trash3')
                                ->confirm('Are you sure you want to delete this consultation option?')
                                ->method('remove')
                                ->parameters([
                                    'consultationOption' => $option->id,
                                ]),
                        ])),
            ]),
        ];
    }

    public function remove(ConsultationOption $consultationOption)
    {
        $consultationOption->delete();
        Alert::info('Consultation option deleted successfully.');

        return redirect()->route('platform.consultation-options.list');
    }
}
