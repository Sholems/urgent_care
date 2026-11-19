<?php

namespace App\Orchid\Screens\Nurse;

use Orchid\Screen\TD;
use App\Models\Nurse;
use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Alert;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\DropDown;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

class NurseListScreen extends Screen {
  public function query(): iterable {
    return [
      'nurses' => Nurse::filters()->defaultSort('updated_at', 'desc')->paginate(10),
    ];
  }

  public function name(): ?string {
    return 'Nurses';
  }

  public function permission(): ?iterable {
    return [
      'platform.systems.users',
      'platform.nurses',
    ];
  }

  public function commandBar(): iterable {
    return [
      Link::make('Create')
        ->icon('bs.plus-circle')
        ->route('platform.nurse.edit')
        ->canSee(Auth::user() && Auth::user()->hasAccess('platform.systems.users')),
    ];
  }

  public function layout(): iterable {
    return [
      Layout::table('nurses', [
        TD::make('id', 'ID')
          ->cantHide()
          ->sort()
          ->align(TD::ALIGN_CENTER)
          ->filter(Input::make())
          ->render(function (Nurse $nurse) {
            return '#' . $nurse->id;
          }),

        TD::make('name', 'Name')
          ->sort()
          ->cantHide()
          ->filter(Input::make())
          ->render(function (Nurse $nurse) {
            $user = Auth::user();
            if ($user && $user->hasAccess('platform.systems.users')) {
              return Link::make($nurse->name)
                ->route('platform.nurse.edit', $nurse);
            }
            return $nurse->name;
          }),

        TD::make('email', 'Email')
          ->sort()
          ->cantHide()
          ->filter(Input::make())
          ->render(function (Nurse $nurse) {
            return $nurse->email;
          }),

        TD::make('phone', 'Phone')
          ->sort()
          ->cantHide()
          ->filter(Input::make())
          ->render(function (Nurse $nurse) {
            return $nurse->phone;
          }),

        TD::make('is_active', 'Active')
          ->cantHide()
          ->sort()
          ->align(TD::ALIGN_CENTER)
          ->render(function (Nurse $nurse) {
            return $nurse->is_active ? '<i class="text-success">●</i>' : '<i class="text-danger">●</i>';
          }),

        TD::make('updated_at', 'Last Updated')
          ->cantHide()
          ->sort()
          ->filter(Input::make())
          ->render(function (Nurse $nurse) {
            return $nurse->updated_at->format('M j, Y');
          }),

        TD::make(__('Actions'))
          ->cantHide()
          ->align(TD::ALIGN_CENTER)
          ->width('100px')
          ->render(function (Nurse $nurse) {
            $user = Auth::user();
            if ($user && $user->hasAccess('platform.systems.users')) {
              return DropDown::make()
                ->icon('bs.three-dots-vertical')
                ->list([
                  Link::make(__('Edit'))
                    ->route('platform.nurse.edit', $nurse->id)
                    ->icon('bs.pencil'),
                  Button::make('Delete')
                    ->icon('bs.trash3')
                    ->confirm('Are you sure you want to delete this nurse? This action cannot be undone.')
                    ->method('remove')
                    ->parameters([
                      'nurse' => $nurse->id,
                    ]),
                ]);
            }
            return '—';
          }),
      ]),
    ];
  }

  public function remove(Nurse $nurse) {
    if (!Auth::user() || !Auth::user()->hasAccess('platform.systems.users')) {
      Alert::error('You do not have permission to delete nurses.');
      return Redirect::route('platform.nurse.list');
    }
    
    $nurse->delete();
    Alert::info('You have successfully deleted the nurse.');
    return Redirect::route('platform.nurse.list');
  }
}

