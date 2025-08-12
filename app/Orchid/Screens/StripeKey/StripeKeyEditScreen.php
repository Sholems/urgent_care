<?php

namespace App\Orchid\Screens\StripeKey;

use App\Models\StripeKey;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Input;
use Orchid\Support\Facades\Alert;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Layout;

class StripeKeyEditScreen extends Screen
{
    public $stripeKey;

    public function query(StripeKey $stripeKey): iterable
    {
        return [
            'stripeKey' => $stripeKey,
        ];
    }

    public function name(): ?string
    {
        return $this->stripeKey->exists ? 'Edit Stripe Key' : 'Create Stripe Key';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Create')
                ->icon('pencil')
                ->method('create')
                ->canSee(!$this->stripeKey->exists),

            Button::make('Update')
                ->icon('note')
                ->method('update')
                ->canSee($this->stripeKey->exists),

            Button::make('Remove')
                ->icon('trash')
                ->method('remove')
                ->canSee($this->stripeKey->exists)
                ->confirm('Are you sure you want to delete this key?'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('stripeKey.publishable_key')
                    ->title('Publishable Key:')
                    ->required(),

                Input::make('stripeKey.secret_key')
                    ->title('Secret Key:')
                    ->required(),
            ]),
        ];
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'stripeKey.publishable_key' => 'required',
            'stripeKey.secret_key' => 'required',
        ]);

        $this->stripeKey->fill($data['stripeKey'])->save();

        Alert::info('You have successfully created the stripe key.');
        return redirect()->route('platform.stripekey.list');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'stripeKey.publishable_key' => 'required',
            'stripeKey.secret_key' => 'required',
        ]);

        $this->stripeKey->fill($data['stripeKey'])->save();

        Alert::info('You have successfully updated the stripe key.');
        return redirect()->route('platform.stripekey.list');
    }

    public function remove(StripeKey $stripeKey)
    {
        $stripeKey->delete();
        Alert::info('You have successfully deleted the stripe key.');
        return redirect()->route('platform.stripekey.list');
    }
}
