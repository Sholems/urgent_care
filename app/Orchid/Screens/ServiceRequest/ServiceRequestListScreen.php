<?php

namespace App\Orchid\Screens\ServiceRequest;

use Orchid\Screen\Screen;
use App\Models\ServiceRequest;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\DropDown;
use Illuminate\Support\Facades\Auth;
use App\Models\Nurse;
use App\Models\Doctor;
use App\Models\RequestAssignment;

class ServiceRequestListScreen extends Screen {
  public function query(): iterable {
    $user = Auth::user();
    $query = ServiceRequest::query()
      ->with(['user', 'payments', 'assignedNurse', 'assignedGP', 'assignedSpecialist']);
    
    // Check if user is a doctor - doctors should ALWAYS only see their assigned requests
    $doctor = $user ? Doctor::where('user_id', $user->id)->first() : null;
    
    if ($doctor) {
      // Get all service request IDs assigned to this doctor (as GP or Specialist)
      $doctorRequestIds = RequestAssignment::whereIn('assigned_to_type', ['gp', 'specialist'])
        ->where('assigned_to_id', $doctor->id)
        ->where('status', 'active')
        ->pluck('service_request_id');
      
      if ($doctorRequestIds->isNotEmpty()) {
        // Filter to only show assigned requests
        $query->whereIn('id', $doctorRequestIds->unique());
      } else {
        // Doctor has no assigned requests, show nothing
        $query->whereRaw('1 = 0'); // Empty result
      }
    } elseif ($user && !$user->hasAccess('platform.systems.users')) {
      // If user is not an admin and not a doctor, check if they're a nurse
      $nurse = Nurse::where('user_id', $user->id)->first();
      
      if ($nurse) {
        // Get all service request IDs assigned to this nurse
        $nurseRequestIds = RequestAssignment::where('assigned_to_type', 'nurse')
          ->where('assigned_to_id', $nurse->id)
          ->where('status', 'active')
          ->pluck('service_request_id');
        
        if ($nurseRequestIds->isNotEmpty()) {
          // Filter to only show assigned requests
          $query->whereIn('id', $nurseRequestIds->unique());
        } else {
          // Nurse has no assigned requests, show nothing
          $query->whereRaw('1 = 0'); // Empty result
        }
      } else {
        // If user is not a nurse or doctor but doesn't have admin access, show nothing
        $query->whereRaw('1 = 0'); // Empty result
      }
    }
    // If user is an admin (and not a doctor), they see all requests (no filtering)
    
    return [
      'service_requests' => $query
        ->defaultSort('created_at', 'desc')
        ->filters()
        ->paginate(20),
    ];
  }

  public function name(): ?string {
    return 'Service Requests';
  }

  public function description(): ?string {
    $user = Auth::user();
    if ($user && !$user->hasAccess('platform.systems.users')) {
      $nurse = Nurse::where('user_id', $user->id)->first();
      $doctor = Doctor::where('user_id', $user->id)->first();
      if ($nurse) {
        return 'View your assigned service requests';
      }
      if ($doctor) {
        return 'View your assigned service requests';
      }
    }
    return 'Manage all urgent care service requests';
  }

  public function permission(): ?iterable {
    return [
      'platform.systems.users',
      'platform.service-requests',
    ];
  }

  public function commandBar(): iterable {
    return [];
  }

  public function layout(): iterable {
    return [
      Layout::table('service_requests', [
        TD::make('id', 'ID')
          ->cantHide()
          ->sort()
          ->render(function (ServiceRequest $request) {
            return Link::make('#' . $request->id)
              ->route('platform.service-request.detail', $request->id);
          }),

        TD::make('reference_number', 'Reference Number')
          ->cantHide()
          ->sort()
          ->filter(Input::make())
          ->render(function (ServiceRequest $request) {
            return Link::make($request->reference_number)
              ->route('platform.service-request.detail', $request->id);
          }),

        TD::make('patient_name', 'Patient Name')
          ->cantHide()
          ->sort()
          ->filter(Input::make())
          ->render(function (ServiceRequest $request) {
            return Link::make($request->patient_name)
              ->route('platform.service-request.detail', $request->id);
          }),

        TD::make('patient_phone', 'Phone')
          ->cantHide()
          ->sort()
          ->filter(Input::make())
          ->render(function (ServiceRequest $request) {
            return $request->patient_phone ?? '—';
          }),

        TD::make('user_name', 'User')
          ->sort()
          ->render(function (ServiceRequest $request) {
            return $request->user?->user_name ?? '—';
          }),

        TD::make('transport_mode', 'Transport')
          ->sort()
          ->render(function (ServiceRequest $request) {
            return ucwords(str_replace('_', ' ', $request->transport_mode ?? 'N/A'));
          }),

        TD::make('status', 'Status')
          ->cantHide()
          ->sort()
          ->align(TD::ALIGN_CENTER)
          ->filter(Select::make()->options([
            'pending' => 'Pending',
            'payment_pending' => 'Payment Pending',
            'payment_confirmed' => 'Payment Confirmed',
            'nurse_assigned' => 'Nurse Assigned',
            'vitals_uploaded' => 'Vitals Uploaded',
            'gp_assigned' => 'GP Assigned',
            'gp_consultation' => 'GP Consultation',
            'specialist_referred' => 'Specialist Referred',
            'specialist_assigned' => 'Specialist Assigned',
            'admitted' => 'Admitted',
            'discharge_pending' => 'Discharge Pending',
            'discharged' => 'Discharged',
            'completed' => 'Completed',
            'terminated' => 'Terminated',
          ]))
          ->render(function (ServiceRequest $request) {
            $status = $request->status ?? 'pending';
            $color = match($status) {
              'pending', 'payment_pending' => 'warning',
              'payment_confirmed', 'completed', 'discharged' => 'success',
              'terminated' => 'danger',
              default => 'info',
            };
            return "<span class='badge bg-{$color}'>" . ucwords(str_replace('_', ' ', $status)) . '</span>';
          }),

        TD::make('created_at', 'Created')
          ->sort()
          ->render(function (ServiceRequest $request) {
            return $request->created_at?->format('Y-m-d H:i') ?? '—';
          }),

        TD::make(__('Actions'))
          ->cantHide()
          ->align(TD::ALIGN_CENTER)
          ->render(fn (ServiceRequest $request) => DropDown::make()
            ->icon('bs.three-dots-vertical')
            ->list([
              Link::make(__('View Details'))
                ->route('platform.service-request.detail', $request->id)
                ->icon('bs.eye'),

              Button::make('Update Status')
                ->icon('bs.arrow-repeat')
                ->method('updateStatus')
                ->parameters([
                  'request' => $request->id,
                ])
                ->canSee(true),

              Button::make('Terminate')
                ->icon('bs.x-circle')
                ->method('terminate')
                ->parameters([
                  'request' => $request->id,
                ])
                ->confirm('Are you sure you want to terminate this service request?')
                ->canSee(
                  Auth::user() && 
                  Auth::user()->hasAccess('platform.systems.users') && 
                  $request->status !== 'terminated' && 
                  $request->status !== 'completed'
                ),
            ])),
      ])
    ];
  }

  public function terminate(ServiceRequest $request) {
    if (!Auth::user() || !Auth::user()->hasAccess('platform.systems.users')) {
      Alert::error('You do not have permission to terminate service requests.');
      return redirect()->route('platform.service-request.list');
    }
    
    $request->update(['status' => 'terminated']);
    Alert::info('Service request terminated');
    return redirect()->route('platform.service-request.list');
  }

  public function updateStatus(ServiceRequest $request) {
    // This can be expanded to show a modal for status selection
    Alert::info('Use the detail screen to update status');
    return redirect()->route('platform.service-request.detail', $request->id);
  }
}


