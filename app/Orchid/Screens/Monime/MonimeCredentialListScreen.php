<?php

namespace App\Orchid\Screens\Monime;

use App\Models\MonimeCredential;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class MonimeCredentialListScreen extends Screen
{
    public $credentials;

    public function query(): iterable
    {
        return [
            'credentials' => MonimeCredential::filters()
                ->defaultSort('updated_at', 'desc')
                ->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Monime Credentials';
    }

    public function description(): ?string
    {
        return 'Manage Monime API credentials and settings.';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Create')
                ->icon('bs.plus-circle')
                ->route('platform.monime.edit')
                ->canSee($this->credentials->count() < 1),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('credentials', [
                TD::make('name', 'Name')
                    ->cantHide(),

                TD::make('space_id', 'Space ID')
                    ->cantHide()
                    ->render(fn (MonimeCredential $credential) => Str::limit($credential->space_id, 40)),

                TD::make('enabled', 'Enabled')
                    ->cantHide()
                    ->render(fn (MonimeCredential $credential) => $credential->enabled ? 'Yes' : 'No'),

                TD::make('api_version', 'API Version')
                    ->cantHide()
                    ->render(fn (MonimeCredential $credential) => $credential->api_version),

                TD::make('updated_at', __('Last edit'))
                    ->sort()
                    ->cantHide()
                    ->filter(Input::make())
                    ->render(fn (MonimeCredential $credential) => $credential->updated_at->format('M j, Y')),

                TD::make(__('Actions'))
                    ->cantHide()
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (MonimeCredential $credential) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(__('Edit'))
                                ->route('platform.monime.edit', $credential->id)
                                ->icon('bs.pencil'),
                            Button::make('Delete')
                                ->icon('bs.trash3')
                                ->confirm('Are you sure you want to delete this Monime credential?')
                                ->method('remove')
                                ->parameters(['credential' => $credential->id]),
                        ])),
            ]),
        ];
    }

    public function remove(MonimeCredential $credential)
    {
        $credential->delete();
        Alert::info('You have successfully deleted the Monime credential.');

        return redirect()->route('platform.monime.list');
    }
}
