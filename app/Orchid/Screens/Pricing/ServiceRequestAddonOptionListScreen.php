<?php

namespace App\Orchid\Screens\Pricing;

use App\Models\ServiceRequestAddonOption;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class ServiceRequestAddonOptionListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'addonOptions' => ServiceRequestAddonOption::filters()
                ->defaultSort('code')
                ->paginate(20),
        ];
    }

    public function name(): ?string
    {
        return 'Service Add-ons';
    }

    public function description(): ?string
    {
        return 'Observation and specialist fees included with urgent care requests.';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Add Add-on')
                ->icon('bs.plus-circle')
                ->route('platform.service-request-addon-options.edit'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('addonOptions', [
                TD::make('label', 'Label')
                    ->sort()
                    ->filter(Input::make())
                    ->render(fn (ServiceRequestAddonOption $option) => Link::make($option->label)
                        ->route('platform.service-request-addon-options.edit', $option->id)),

                TD::make('code', 'Code')
                    ->sort()
                    ->filter(Input::make()),

                TD::make('amount', 'Price')
                    ->sort()
                    ->render(fn (ServiceRequestAddonOption $option) => number_format((float) $option->amount, 2)),

                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (ServiceRequestAddonOption $option) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(__('Edit'))
                                ->route('platform.service-request-addon-options.edit', $option->id)
                                ->icon('bs.pencil'),
                            Button::make('Delete')
                                ->icon('bs.trash3')
                                ->confirm('Are you sure you want to delete this add-on?')
                                ->method('remove')
                                ->parameters([
                                    'serviceRequestAddonOption' => $option->id,
                                ]),
                        ])),
            ]),
        ];
    }

    public function remove(ServiceRequestAddonOption $serviceRequestAddonOption)
    {
        $serviceRequestAddonOption->delete();
        Alert::info('Add-on deleted successfully.');

        return redirect()->route('platform.service-request-addon-options.list');
    }
}
