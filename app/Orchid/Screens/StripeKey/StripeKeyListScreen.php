<?php

namespace App\Orchid\Screens\StripeKey;

use App\Models\StripeKey;
use Illuminate\Support\Str;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\DropDown;

class StripeKeyListScreen extends Screen
{
    public $stripeKeys;

    public function query(): iterable
    {
        return [
            'stripeKeys' => StripeKey::filters()->defaultSort('updated_at', 'desc')->paginate()
        ];
    }

    public function name(): ?string
    {
        return 'Stripe Keys';
    }

    public function description(): ?string
    {
        return 'Manage Stripe API keys.';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Create')
                ->icon('bs.plus-circle')
                ->route('platform.stripekey.edit')
                ->canSee($this->stripeKeys->count() < 1),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('stripeKeys', [
                TD::make('publishable_key', 'Publishable Key')
                    ->cantHide()
                    ->render(fn (StripeKey $key) => Str::limit($key->publishable_key, 40)),

                TD::make('updated_at', __('Last edit'))
                    ->sort()
                    ->cantHide()
                    ->filter(Input::make())
                    ->render(fn (StripeKey $key) => $key->updated_at->format('M j, Y')),

                TD::make(__('Actions'))
                    ->cantHide()
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (StripeKey $key) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(__('Edit'))
                                ->route('platform.stripekey.edit', $key->id)
                                ->icon('bs.pencil'),

                            Button::make('Delete')
                                ->icon('bs.trash3')
                                ->confirm('Are you sure you want to delete this key?')
                                ->method('remove')
                                ->parameters(['stripeKey' => $key->id]),
                        ])),
            ])
        ];
    }

    public function remove(StripeKey $stripeKey)
    {
        $stripeKey->delete();
        Alert::info('You have successfully deleted the stripe key.');
        return redirect()->route('platform.stripekey.list');
    }
}
