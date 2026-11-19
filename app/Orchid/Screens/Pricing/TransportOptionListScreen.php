<?php

namespace App\Orchid\Screens\Pricing;

use App\Models\TransportOption;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class TransportOptionListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'transportOptions' => TransportOption::filters()
                ->defaultSort('updated_at', 'desc')
                ->paginate(20),
        ];
    }

    public function name(): ?string
    {
        return 'Transport Options';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Add Transport Option')
                ->icon('bs.plus-circle')
                ->route('platform.transport-options.edit'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('transportOptions', [
                TD::make('label', 'Label')
                    ->sort()
                    ->filter(Input::make())
                    ->render(fn (TransportOption $option) => Link::make($option->label)
                        ->route('platform.transport-options.edit', $option->id)),

                TD::make('code', 'Code')
                    ->sort()
                    ->filter(Input::make()),

                TD::make('amount', 'Price')
                    ->sort()
                    ->render(fn (TransportOption $option) => number_format((float) $option->amount, 2)),

                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (TransportOption $option) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(__('Edit'))
                                ->route('platform.transport-options.edit', $option->id)
                                ->icon('bs.pencil'),
                            Button::make('Delete')
                                ->icon('bs.trash3')
                                ->confirm('Are you sure you want to delete this transport option?')
                                ->method('remove')
                                ->parameters([
                                    'transportOption' => $option->id,
                                ]),
                        ])),
            ]),
        ];
    }

    public function remove(TransportOption $transportOption)
    {
        $transportOption->delete();
        Alert::info('Transport option deleted successfully.');

        return redirect()->route('platform.transport-options.list');
    }
}
