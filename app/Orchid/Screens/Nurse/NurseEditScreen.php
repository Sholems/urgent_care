<?php

namespace App\Orchid\Screens\Nurse;

use Orchid\Screen\Screen;
use App\Models\Nurse;
use App\Models\User;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Switcher;
use Illuminate\Support\Facades\Redirect;
use Orchid\Platform\Models\Role;
use Illuminate\Support\Facades\Auth;

class NurseEditScreen extends Screen {
  public $nurse;
  public $users;

  public function query(Nurse $nurse): iterable {
    return [
      'nurse' => $nurse,
      'users' => User::all(),
    ];
  }

  public function name(): ?string {
    return $this->nurse->exists ? 'Edit Nurse' : 'Create New Nurse';
  }

  public function permission(): ?iterable {
    return [
      'platform.systems.users',
      'platform.nurses',
    ];
  }

  public function commandBar(): iterable {
    $user = Auth::user();
    $isAdmin = $user && $user->hasAccess('platform.systems.users');
    
    return [
      Button::make('Create Nurse')
        ->icon('bs.plus-circle')
        ->method('create')
        ->canSee(!$this->nurse->exists && $isAdmin),

      Button::make('Update')
        ->icon('bs.check-circle')
        ->method('update')
        ->canSee($this->nurse->exists && $isAdmin),

      Button::make('Remove')
        ->icon('bs.trash3')
        ->method('remove')
        ->canSee($this->nurse->exists && $isAdmin)
        ->confirm('Are you sure you want to delete this nurse? This action cannot be undone.'),
    ];
  }

  public function layout(): iterable {
    return [
      Layout::rows([
        Input::make('nurse.name')
          ->title('Name:')
          ->required(!$this->nurse->exists)
          ->placeholder('Enter nurse name'),

        Input::make('nurse.email')
          ->title('Email:')
          ->required(!$this->nurse->exists)
          ->type('email')
          ->placeholder('Enter email address'),

        Input::make('nurse.phone')
          ->title('Phone:')
          ->required(!$this->nurse->exists)
          ->placeholder('Enter phone number'),

        Select::make('nurse.user_id')
          ->title('User:')
          ->disabled(!$this->users->count())
          ->fromModel(User::class, 'name', 'id')
          ->value($this->nurse->user_id)
          ->empty($this->users->count() ? 'Select user' : 'Please create users first in the "Users" section.')
          ->placeholder($this->users->count() ? 'Select user' : 'Please create users first in the "Users" section.'),

        Switcher::make('nurse.is_active')
          ->title('Active Status')
          ->sendTrueOrFalse()
          ->placeholder('Nurse is active')
          ->value($this->nurse->exists ? $this->nurse->is_active : true),
      ]),
    ];
  }

  public function create(Request $request) {
    if (!Auth::user() || !Auth::user()->hasAccess('platform.systems.users')) {
      Alert::error('You do not have permission to create nurses.');
      return Redirect::route('platform.nurse.list');
    }
    
    $validated = $request->validate([
      'nurse.name' => 'required|string|max:255',
      'nurse.email' => 'required|email|unique:nurses,email',
      'nurse.phone' => 'required|string|max:255',
      'nurse.user_id' => 'required|exists:users,id',
      'nurse.is_active' => 'boolean',
    ]);

    // Get the user
    $user = User::findOrFail($request->input('nurse.user_id'));

    // Create or get Nurse role
    $nurseRole = Role::firstOrCreate(
      ['slug' => 'nurse'],
      [
        'name' => 'Nurse',
        'permissions' => [
          'platform.index' => true,
          'platform.service-requests' => true,
          'platform.nurses' => true,
        ],
      ]
    );

    // Assign Nurse role to the user using sync to ensure proper relationship
    $user->roles()->sync([$nurseRole->id]);

    // Also ensure permissions are set
    $permissions = $user->permissions ?? [];
    $permissions['platform.index'] = true;
    $permissions['platform.service-requests'] = true;
    $permissions['platform.nurses'] = true;
    $user->permissions = $permissions;
    $user->save();

    // Create nurse
    $nurse = Nurse::create([
      'user_id' => $request->input('nurse.user_id'),
      'name' => $request->input('nurse.name'),
      'email' => $request->input('nurse.email'),
      'phone' => $request->input('nurse.phone'),
      'is_active' => $request->input('nurse.is_active', true),
    ]);

    Alert::info('Nurse created successfully.');
    return Redirect::route('platform.nurse.edit', $nurse);
  }

  public function update(Request $request, Nurse $nurse) {
    if (!Auth::user() || !Auth::user()->hasAccess('platform.systems.users')) {
      Alert::error('You do not have permission to update nurses.');
      return Redirect::route('platform.nurse.list');
    }
    
    $validated = $request->validate([
      'nurse.name' => 'required|string|max:255',
      'nurse.email' => 'required|email|unique:nurses,email,' . $nurse->id,
      'nurse.phone' => 'required|string|max:255',
      'nurse.user_id' => 'nullable|exists:users,id',
      'nurse.is_active' => 'boolean',
    ]);

    $nurse->update([
      'name' => $request->input('nurse.name'),
      'email' => $request->input('nurse.email'),
      'phone' => $request->input('nurse.phone'),
      'user_id' => $request->input('nurse.user_id', $nurse->user_id),
      'is_active' => $request->input('nurse.is_active', true),
    ]);

    Alert::info('Nurse updated successfully.');
    return Redirect::route('platform.nurse.edit', $nurse);
  }

  public function remove(Nurse $nurse) {
    if (!Auth::user() || !Auth::user()->hasAccess('platform.systems.users')) {
      Alert::error('You do not have permission to delete nurses.');
      return Redirect::route('platform.nurse.list');
    }
    
    $nurse->delete();
    Alert::info('Nurse deleted successfully.');
    return Redirect::route('platform.nurse.list');
  }
}

