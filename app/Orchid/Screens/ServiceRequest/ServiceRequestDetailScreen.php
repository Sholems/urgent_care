<?php

namespace App\Orchid\Screens\ServiceRequest;

use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Screen\Sight;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Modal;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Support\Facades\Alert;
use App\Models\ServiceRequest;
use App\Models\RequestPayment;
use App\Models\VitalsReport;
use App\Models\PatientFile;
use App\Models\Referral;
use App\Models\Admission;
use App\Models\Nurse;
use App\Models\Doctor;
use App\Models\RequestAssignment;
use App\Services\ServiceRequestService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ServiceRequestDetailScreen extends Screen {

  public $serviceRequest;
  public $nurses;
  public $currentNurse;
  public $currentDoctor;
  public $doctors;

  public function query(ServiceRequest $serviceRequest): iterable {
    // Load basic relationships
    // Note: We don't eager load assignedNurse.assignedTo, assignedGP.assignedTo, etc.
    // because these relationships can be null, and eager loading nested relationships on null causes errors
    $this->serviceRequest = $serviceRequest->load([
      'user',
      'payments',
      'vitalsReports.nurse',
      'patientFiles',
      'referrals.gpDoctor',
      'referrals.specialistDoctor',
      'admissions.specialistDoctor',
      'assignments',
    ]);

    // Get nurses for the dropdown
    $activeNurses = Nurse::where('is_active', true)->get();
    $this->nurses = $activeNurses->isNotEmpty() ? $activeNurses : Nurse::all();
    
    // Get current user and check if they're a nurse or doctor
    $user = Auth::user();
    $currentNurse = null;
    $currentDoctor = null;
    
    if ($user) {
      if (!$user->hasAccess('platform.systems.users')) {
        $currentNurse = Nurse::where('user_id', $user->id)->first();
      }
      
      // Check if user is a doctor and if they're assigned to this service request
      $doctor = Doctor::where('user_id', $user->id)->first();
      if ($doctor) {
        // Check if this doctor is assigned to this service request (as GP or Specialist)
        $assignment = RequestAssignment::where('service_request_id', $serviceRequest->id)
          ->whereIn('assigned_to_type', ['gp', 'specialist'])
          ->where('assigned_to_id', $doctor->id)
          ->where('status', 'active')
          ->first();
        
        if ($assignment) {
          $currentDoctor = $doctor;
        }
      }
    }
    
    $this->currentNurse = $currentNurse;
    $this->currentDoctor = $currentDoctor;
    
    // Store doctors in property for use in closures
    $this->doctors = Doctor::where('is_available', true)->get();

    return [
      'service_request' => $this->serviceRequest,
      'payments' => $this->serviceRequest->payments,
      'vitals_reports' => $this->serviceRequest->vitalsReports,
      'patient_files' => $this->serviceRequest->patientFiles,
      'referrals' => $this->serviceRequest->referrals,
      'admissions' => $this->serviceRequest->admissions,
      'nurses' => $this->nurses,
      'doctors' => $this->doctors,
      'current_nurse' => $currentNurse,
    ];
  }

  public function name(): ?string {
    return 'Service Request Details';
  }

  public function description(): ?string {
    return $this->serviceRequest->reference_number ?? 'Service Request';
  }

  public function permission(): ?iterable {
    return [
      'platform.systems.users',
      'platform.service-requests',
    ];
  }

  public function commandBar(): iterable {
    return [
      Button::make('Terminate')
        ->icon('bs.x-circle')
        ->method('terminate')
        ->parameters([
          'serviceRequest' => $this->serviceRequest->id,
        ])
        ->confirm('Are you sure you want to terminate this service request?')
        ->canSee(
          Auth::user() && 
          Auth::user()->hasAccess('platform.systems.users') && 
          $this->serviceRequest->status !== 'terminated' && 
          $this->serviceRequest->status !== 'completed'
        ),
    ];
  }

  public function layout(): iterable {
    $latestVitals = $this->serviceRequest->vitalsReports->first()?->vitals_data ?? [];
    return [
      Layout::legend('service_request', [
        Sight::make('id', 'ID:')->render(function (ServiceRequest $request) {
          return '#' . $request->id;
        }),
        Sight::make('reference_number', 'Reference Number:'),
        Sight::make('status', 'Status:')->render(function (ServiceRequest $request) {
          $status = $request->status ?? 'pending';
          $color = match($status) {
            'pending', 'payment_pending' => 'warning',
            'payment_confirmed', 'completed', 'discharged' => 'success',
            'terminated' => 'danger',
            default => 'info',
          };
          return "<span class='badge bg-{$color}'>" . ucwords(str_replace('_', ' ', $status)) . '</span>';
        }),
        Sight::make('patient_name', 'Patient Name:'),
        Sight::make('patient_phone', 'Patient Phone:'),
        Sight::make('patient_address', 'Patient Address:')->render(function (ServiceRequest $request) {
          return $request->patient_address ?? '—';
        }),
        Sight::make('patient_gender', 'Patient Gender:')->render(function (ServiceRequest $request) {
          return ucfirst($request->patient_gender ?? '—');
        }),
        Sight::make('transport_mode', 'Transport Mode:')->render(function (ServiceRequest $request) {
          return ucwords(str_replace('_', ' ', $request->transport_mode ?? 'N/A'));
        }),
        Sight::make('symptoms', 'Symptoms:')->render(function (ServiceRequest $request) {
          if (!$request->symptoms) {
            return '—';
          }
          $symptoms = is_string($request->symptoms) ? json_decode($request->symptoms, true) : $request->symptoms;
          return is_array($symptoms) ? implode(', ', $symptoms) : $request->symptoms;
        }),
        Sight::make('user_name', 'User:')->render(function (ServiceRequest $request) {
          return $request->user?->user_name ?? '—';
        }),
        Sight::make('user_email', 'User Email:')->render(function (ServiceRequest $request) {
          return $request->user?->user_email ?? '—';
        }),
        Sight::make('created_at', 'Created At:')->render(function (ServiceRequest $request) {
          return $request->created_at?->format('Y-m-d H:i:s') ?? '—';
        }),
        Sight::make('updated_at', 'Updated At:')->render(function (ServiceRequest $request) {
          return $request->updated_at?->format('Y-m-d H:i:s') ?? '—';
        }),
        Sight::make('assigned_nurse', 'Assigned Nurse:')->render(function (ServiceRequest $request) {
          $assignment = $request->assignedNurse;
          if ($assignment && method_exists($assignment, 'assignedTo')) {
            $nurse = $assignment->assignedTo;
            return $nurse ? $nurse->name . ' (' . $nurse->email . ')' : '—';
          }
          return '—';
        }),
        Sight::make('assigned_gp', 'Assigned GP:')->render(function (ServiceRequest $request) {
          $assignment = $request->assignedGP;
          if ($assignment && method_exists($assignment, 'assignedTo')) {
            $doctor = $assignment->assignedTo;
            return $doctor ? $doctor->name . ' (' . $doctor->email . ')' : '—';
          }
          return '—';
        }),
      ]),

      Layout::rows([
        Select::make('status')
          ->title('Update Status')
          ->options([
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
          ])
          ->value($this->serviceRequest->status),
        Button::make('Update Status')
          ->method('updateStatus')
          ->parameters([
            'serviceRequest' => $this->serviceRequest->id,
          ])
          ->icon('bs.check-circle')
          ->confirm('Are you sure you want to update the status?'),
      ])->title('Status Management'),

      Layout::rows([
        Select::make('nurse_id')
          ->title('Assign Nurse')
          ->options(function () {
            $nurses = $this->nurses ?? collect();
            return $nurses->mapWithKeys(function ($nurse) {
              $label = $nurse->name . ' (' . $nurse->email . ')';
              if (!$nurse->is_active) {
                $label .= ' [Inactive]';
              }
              return [$nurse->id => $label];
            })->toArray();
          })
          ->empty('Please select nurses or create nurses first.')
          ->value($this->getCurrentNurseId())
          ->help('Select a nurse to assign to this request'),
        Button::make('Assign Nurse')
          ->method('assignNurse')
          ->parameters([
            'serviceRequest' => $this->serviceRequest->id,
          ])
          ->icon('bs.person-plus')
          ->confirm('Assign this nurse to the service request?')
          ->canSee($this->serviceRequest->status === 'payment_confirmed' || $this->serviceRequest->status === 'pending' || $this->serviceRequest->status === 'nurse_assigned'),
      ])->title('Nurse Assignment'),

      Layout::rows([
        Select::make('doctor_id')
          ->title('Assign GP Doctor')
          ->options(function () {
            return Doctor::where('is_available', true)
              ->get()
              ->mapWithKeys(function ($doctor) {
                return [$doctor->id => $doctor->name . ' (' . $doctor->email . ')'];
              })
              ->toArray();
          })
          ->empty('Select a GP doctor')
          ->value($this->getCurrentGPId())
          ->help('Select a GP doctor to assign to this request'),
        Button::make('Assign GP Doctor')
          ->method('assignGP')
          ->parameters([
            'serviceRequest' => $this->serviceRequest->id,
          ])
          ->icon('bs.person-plus')
          ->confirm('Assign this GP doctor to the service request?')
          ->canSee($this->serviceRequest->status === 'vitals_uploaded' || $this->serviceRequest->status === 'nurse_assigned' || $this->serviceRequest->status === 'gp_assigned'),
      ])->title('GP Assignment'),

      // Admin Actions - Upload Patient File (only admins)
      Layout::rows([
        Select::make('patient_file_type')
          ->title('File Type')
          ->options([
            'medical_report' => 'Medical Report',
            'prescription' => 'Prescription',
            'scan_result' => 'Scan Result',
            'lab_result' => 'Lab Result',
            'xray' => 'X-Ray',
            'other' => 'Other',
          ])
          ->empty('Select file type')
          ->help('Select the type of patient file'),
        Input::make('patient_file')
          ->title('Patient File')
          ->type('file')
          ->help('Upload a patient file (image, PDF, or document)'),
        TextArea::make('patient_file_description')
          ->title('Description')
          ->rows(3)
          ->placeholder('Enter file description (optional)'),
        Button::make('Upload Patient File')
          ->method('uploadPatientFile')
          ->parameters([
            'serviceRequest' => $this->serviceRequest->id,
          ])
          ->icon('bs.upload')
          ->confirm('Upload this patient file?'),
      ])
        ->title('Upload Patient File')
        ->canSee(
          ($user = Auth::user()) && (
            $user->hasAccess('platform.systems.users') || // Admins
            ($this->currentDoctor !== null) // Assigned doctors
          )
        ),

      // Nurse Actions - Upload Vitals Report (only nurses)
      Layout::rows([
        Input::make('blood_pressure')
          ->title('Blood Pressure')
          ->placeholder('e.g., 120/80')
          ->help('Enter blood pressure reading'),
        Input::make('heart_rate')
          ->title('Heart Rate (bpm)')
          ->type('number')
          ->placeholder('e.g., 72')
          ->help('Enter heart rate in beats per minute'),
        Input::make('temperature')
          ->title('Temperature (°F)')
          ->type('number')
          ->placeholder('e.g., 98.6')
          ->help('Enter temperature in Fahrenheit'),
        Input::make('oxygen_saturation')
          ->title('Oxygen Saturation (%)')
          ->type('number')
          ->placeholder('e.g., 98')
          ->help('Enter oxygen saturation percentage'),
        Input::make('respiratory_rate')
          ->title('Respiratory Rate (per minute)')
          ->type('number')
          ->placeholder('e.g., 16')
          ->help('Enter respiratory rate per minute'),
        Input::make('weight')
          ->title('Weight (kg)')
          ->type('number')
          ->placeholder('e.g., 70')
          ->help('Enter weight in kilograms (optional)'),
        Input::make('height')
          ->title('Height (cm)')
          ->type('number')
          ->placeholder('e.g., 170')
          ->help('Enter height in centimeters (optional)'),
        TextArea::make('vitals_notes')
          ->title('Additional Notes')
          ->rows(3)
          ->placeholder('Enter any additional clinical notes or observations (optional)'),
        Input::make('vitals_report_file')
          ->title('Vitals Report File (Optional)')
          ->type('file')
          ->help('Optional: Upload a vitals report file (image or PDF)'),
        Button::make('Upload Vitals Report')
          ->method('uploadVitalsReport')
          ->parameters([
            'serviceRequest' => $this->serviceRequest->id,
          ])
          ->icon('bs.heart-pulse')
          ->confirm('Upload this vitals report?'),
      ])
        ->title('Upload Vitals Report')
        ->canSee(
          ($user = Auth::user()) && 
          !$user->hasAccess('platform.systems.users') && // Not an admin
          ($this->currentNurse !== null || Nurse::where('user_id', $user->id)->exists()) // Is a nurse
        ),

      // Create Referral - button opens form modal
      Layout::rows([
        ModalToggle::make('Create Referral')
          ->modal('createReferralModal')
          ->method('createReferral')
          ->parameters(['serviceRequest' => $this->serviceRequest->id])
          ->icon('bs.person-plus')
          ->class('btn btn-primary'),
      ])
        ->title('Create Referral')
        ->canSee(
          ($user = Auth::user()) && (
            $user->hasAccess('platform.systems.users') ||
            ($this->currentDoctor !== null)
          )
        ),

      // Create Referral - form modal (full medical referral form)
      Layout::modal('createReferralModal', Layout::rows([
        Select::make('referral_specialist_doctor_id')
          ->title('Refer To (Specialist Doctor) *')
          ->options(function () {
            return Doctor::where('is_available', true)
              ->get()
              ->mapWithKeys(function ($doctor) {
                return [$doctor->id => $doctor->name . ' (' . $doctor->email . ')'];
              })
              ->toArray();
          })
          ->empty('Select specialist doctor'),
        TextArea::make('referral_notes')
          ->title('Referral Notes (optional)')
          ->rows(3)
          ->placeholder('Extra notes for the specialist'),
        Input::make('referral_patient_name')
          ->title('Full Name')
          ->value($this->serviceRequest->patient_name ?? '')
          ->placeholder('Patient full name'),
        Input::make('referral_dob_age')
          ->title('Date of Birth / Age')
          ->placeholder('e.g. 1990-01-15 or 34 yrs'),
        Input::make('referral_sex')
          ->title('Sex')
          ->value(ucfirst($this->serviceRequest->patient_gender ?? ''))
          ->placeholder('Male / Female / Other'),
        Input::make('referral_phone')
          ->title('Phone')
          ->value($this->serviceRequest->patient_phone ?? '')
          ->placeholder('Patient phone'),
        TextArea::make('referral_address')
          ->title('Address')
          ->value($this->serviceRequest->patient_address ?? '')
          ->rows(2)
          ->placeholder('Patient address'),
        Input::make('referral_emergency_contact')
          ->title('Emergency Contact')
          ->placeholder('Emergency contact'),
        Input::make('referral_service_name')
          ->title('Service Name')
          ->placeholder('Referring facility/service name'),
        Input::make('referral_service_address')
          ->title('Service Address')
          ->placeholder('Facility address'),
        Input::make('referral_service_phone')
          ->title('Service Phone')
          ->placeholder('Facility phone'),
        Input::make('referral_referring_doctor_name')
          ->title('Referring Doctor Name')
          ->value($this->currentDoctor ? $this->currentDoctor->name : '')
          ->placeholder('Your name'),
        Input::make('referral_medical_council_number')
          ->title('Medical Council Number')
          ->placeholder('Council registration number'),
        Input::make('referral_referring_doctor_contact')
          ->title('Doctor Phone / Email')
          ->value($this->currentDoctor ? $this->currentDoctor->email : '')
          ->placeholder('Contact'),
        Input::make('referral_date_of_referral')
          ->title('Date of Referral')
          ->value(now()->format('Y-m-d'))
          ->placeholder('Y-m-d'),
        TextArea::make('referral_chief_complaint')
          ->title('Chief Complaint')
          ->value($this->serviceRequest->symptoms ?? '')
          ->rows(3)
          ->placeholder('Chief complaint'),
        TextArea::make('referral_history_present_illness')
          ->title('History of Present Illness')
          ->value($this->serviceRequest->symptoms ?? '')
          ->rows(3)
          ->placeholder('History of present illness'),
        Input::make('referral_temperature')
          ->title('Temperature (°C)')
          ->value(isset($latestVitals['temperature']) ? (string) $latestVitals['temperature'] : '')
          ->placeholder('e.g. 36.6'),
        Input::make('referral_pulse_rate')
          ->title('Pulse (bpm)')
          ->value(isset($latestVitals['heart_rate']) ? (string) $latestVitals['heart_rate'] : '')
          ->placeholder('e.g. 72'),
        Input::make('referral_respiratory_rate')
          ->title('Respiratory Rate (/min)')
          ->value(isset($latestVitals['respiratory_rate']) ? (string) $latestVitals['respiratory_rate'] : '')
          ->placeholder('e.g. 16'),
        Input::make('referral_blood_pressure')
          ->title('Blood Pressure (mmHg)')
          ->value(isset($latestVitals['blood_pressure']) ? (string) $latestVitals['blood_pressure'] : '')
          ->placeholder('e.g. 120/80'),
        Input::make('referral_oxygen_saturation')
          ->title('Oxygen Saturation (%)')
          ->value(isset($latestVitals['oxygen_saturation']) ? (string) $latestVitals['oxygen_saturation'] : '')
          ->placeholder('e.g. 98'),
        Input::make('referral_random_blood_sugar')
          ->title('Random Blood Sugar (mmol/L)')
          ->placeholder('e.g. 5.4'),
        TextArea::make('referral_examination_findings')
          ->title('Examination Findings')
          ->rows(3)
          ->placeholder('Examination findings'),
        Input::make('referral_provisional_diagnosis')
          ->title('Provisional Diagnosis')
          ->placeholder('Provisional diagnosis'),
        Input::make('referral_differential_diagnosis_1')
          ->title('Differential Diagnosis 1')
          ->placeholder('Differential 1'),
        Input::make('referral_differential_diagnosis_2')
          ->title('Differential Diagnosis 2')
          ->placeholder('Differential 2'),
        Input::make('referral_differential_diagnosis_3')
          ->title('Differential Diagnosis 3')
          ->placeholder('Differential 3'),
        TextArea::make('referral_investigations_done')
          ->title('Investigations Done')
          ->rows(2)
          ->placeholder('Investigations performed'),
        Input::make('referral_medications')
          ->title('Medications')
          ->placeholder('Medications given'),
        Input::make('referral_procedures')
          ->title('Procedures')
          ->placeholder('Procedures performed'),
        TextArea::make('referral_reason_for_referral')
          ->title('Reason for Referral')
          ->rows(3)
          ->placeholder('Reason for referral'),
        Input::make('referral_receiving_facility_name')
          ->title('Receiving Facility Name')
          ->placeholder('Receiving facility'),
        Input::make('referral_department_specialty')
          ->title('Department / Specialty')
          ->placeholder('e.g. Cardiology'),
        Select::make('referral_urgency_level')
          ->title('Urgency Level')
          ->options([
            'routine' => 'Routine',
            'urgent' => 'Urgent',
            'emergency' => 'Emergency',
          ])
          ->empty('Select urgency')
          ->placeholder('Urgency'),
        TextArea::make('referral_additional_notes')
          ->title('Additional Notes')
          ->rows(3)
          ->placeholder('Any additional notes'),
      ]))
        ->title('Create Referral')
        ->applyButton('Create Referral')
        ->closeButton('Cancel')
        ->size(Modal::SIZE_LG),

      // Create Admission - Complete Form
      Layout::rows([
        Select::make('admission_specialist_doctor_id')
          ->title('Specialist Doctor')
          ->options(function () {
            return Doctor::where('is_available', true)
              ->get()
              ->mapWithKeys(function ($doctor) {
                return [$doctor->id => $doctor->name . ' (' . $doctor->email . ')'];
              })
              ->toArray();
          })
          ->empty('Select specialist doctor')
          ->help('Select the specialist doctor for admission'),
        DateTimer::make('admission_date')
          ->title('Admission Date')
          ->format('Y-m-d')
          ->help('Set the admission date'),
        Input::make('expected_days')
          ->title('Expected Days')
          ->type('number')
          ->placeholder('Enter expected number of days')
          ->help('Expected number of days for admission'),
        Button::make('Create Admission')
          ->method('createAdmission')
          ->parameters([
            'serviceRequest' => $this->serviceRequest->id,
          ])
          ->icon('bs.hospital')
          ->confirm('Create this admission?'),
      ])
        ->title('Create Admission')
        ->canSee(
          ($user = Auth::user()) && (
            $user->hasAccess('platform.systems.users') || // Admins
            ($this->currentDoctor !== null) // Assigned doctors
          )
        ),

      Layout::table('payments', [
        TD::make('id', 'ID'),
        TD::make('payment_type', 'Type')->render(function (RequestPayment $payment) {
          return ucwords(str_replace('_', ' ', $payment->payment_type));
        }),
        TD::make('amount', 'Amount')->render(function (RequestPayment $payment) {
          return 'Le ' . number_format($payment->amount ?? 0, 2);
        }),
        TD::make('payment_status', 'Status')->render(function (RequestPayment $payment) {
          $status = $payment->payment_status ?? 'pending';
          $color = $status === 'confirmed' ? 'success' : ($status === 'pending' ? 'warning' : 'danger');
          return "<span class='badge bg-{$color}'>" . ucfirst($status) . '</span>';
        }),
        TD::make('payment_method', 'Method')->render(function (RequestPayment $payment) {
          return ucfirst($payment->payment_method ?? '—');
        }),
        TD::make('created_at', 'Date')->render(function (RequestPayment $payment) {
          return $payment->created_at?->format('Y-m-d H:i') ?? '—';
        }),
      ])->title('Payment History'),

      Layout::table('vitals_reports', [
        TD::make('id', 'ID'),
        TD::make('nurse_id', 'Nurse')->render(function (VitalsReport $report) {
          // Check if nurse relationship exists
          if (method_exists($report, 'nurse')) {
            return $report->nurse?->name ?? '—';
          }
          return '—';
        }),
        TD::make('vitals_data', 'Vitals Data')->render(function (VitalsReport $report) {
          $data = is_string($report->vitals_data) ? json_decode($report->vitals_data, true) : $report->vitals_data;
          if (is_array($data)) {
            return implode(', ', array_map(fn($k, $v) => "$k: $v", array_keys($data), $data));
          }
          return $report->vitals_data ?? '—';
        }),
        TD::make('uploaded_at', 'Uploaded')->render(function (VitalsReport $report) {
          return $report->uploaded_at?->format('Y-m-d H:i') ?? '—';
        }),
      ])->title('Vitals Reports'),

      Layout::table('patient_files', [
        TD::make('id', 'ID'),
        TD::make('file_type', 'Type')->render(function (PatientFile $file) {
          return ucwords(str_replace('_', ' ', $file->file_type));
        }),
        TD::make('file_path', 'File')->render(function (PatientFile $file) {
          if (!$file->file_path) {
            return '—';
          }
          return Link::make('Download')
            ->href(Storage::disk('public')->url($file->file_path))
            ->target('_blank');
        }),
        TD::make('description', 'Description'),
        TD::make('created_at', 'Uploaded')->render(function (PatientFile $file) {
          return $file->created_at?->format('Y-m-d H:i') ?? '—';
        }),
      ])->title('Patient Files'),

      Layout::table('referrals', [
        TD::make('id', 'ID'),
        TD::make('gp_doctor_id', 'GP Doctor')->render(function (Referral $referral) {
          if (method_exists($referral, 'gpDoctor')) {
            return $referral->gpDoctor?->name ?? '—';
          }
          return '—';
        }),
        TD::make('specialist_doctor_id', 'Specialist')->render(function (Referral $referral) {
          if (method_exists($referral, 'specialistDoctor')) {
            return $referral->specialistDoctor?->name ?? '—';
          }
          return '—';
        }),
        TD::make('status', 'Status')->render(function (Referral $referral) {
          $status = $referral->status ?? 'pending';
          $color = $status === 'accepted' ? 'success' : ($status === 'rejected' ? 'danger' : 'warning');
          return "<span class='badge bg-{$color}'>" . ucfirst($status) . '</span>';
        }),
        TD::make('referral_notes', 'Notes'),
        TD::make('referral_data', 'Referral form')->render(function (Referral $referral) {
          $data = $referral->referral_data;
          if (empty($data) || !is_array($data)) {
            return '—';
          }
          return view('referral-view-button-and-modal', ['referral' => $referral])->render();
        }),
        TD::make('created_at', 'Created')->render(function (Referral $referral) {
          return $referral->created_at?->format('Y-m-d H:i') ?? '—';
        }),
      ])->title('Referrals'),

      Layout::table('admissions', [
        TD::make('id', 'ID'),
        TD::make('specialist_doctor_id', 'Specialist')->render(function (Admission $admission) {
          if (method_exists($admission, 'specialistDoctor')) {
            return $admission->specialistDoctor?->name ?? '—';
          }
          return '—';
        }),
        TD::make('admission_date', 'Admission Date')->render(function (Admission $admission) {
          return $admission->admission_date?->format('Y-m-d') ?? '—';
        }),
        TD::make('expected_days', 'Expected Days'),
        TD::make('actual_days', 'Actual Days'),
        TD::make('discharge_date', 'Discharge Date')->render(function (Admission $admission) {
          return $admission->discharge_date?->format('Y-m-d') ?? '—';
        }),
        TD::make('status', 'Status')->render(function (Admission $admission) {
          $status = $admission->status ?? 'admitted';
          $color = $status === 'discharged' ? 'success' : 'info';
          return "<span class='badge bg-{$color}'>" . ucfirst($status) . '</span>';
        }),
      ])->title('Admissions'),
    ];
  }

  public function updateStatus(ServiceRequest $serviceRequest) {
    $status = request()->input('status');
    if ($status && $status !== $serviceRequest->status) {
      $serviceRequest->update(['status' => $status]);
      
      // If status is updated to payment_confirmed, update all pending payments to confirmed
      if ($status === 'payment_confirmed') {
        RequestPayment::where('service_request_id', $serviceRequest->id)
          ->where('payment_status', 'pending')
          ->update(['payment_status' => 'confirmed']);
      }
      
      Alert::info('Status updated successfully');
    } elseif ($status === $serviceRequest->status) {
      Alert::info('Status is already set to ' . ucwords(str_replace('_', ' ', $status)));
    }
    return redirect()->route('platform.service-request.detail', $serviceRequest->id);
  }

  public function terminate(ServiceRequest $serviceRequest) {
    if (!Auth::user() || !Auth::user()->hasAccess('platform.systems.users')) {
      Alert::error('You do not have permission to terminate service requests.');
      return redirect()->route('platform.service-request.detail', $serviceRequest->id);
    }
    
    $serviceRequest->update(['status' => 'terminated']);
    Alert::info('Service request terminated');
    return redirect()->route('platform.service-request.list');
  }

  public function assignNurse(ServiceRequest $serviceRequest) {
    $nurseId = request()->input('nurse_id');
    
    if (!$nurseId) {
      Alert::error('Please select a nurse');
      return redirect()->route('platform.service-request.detail', $serviceRequest->id);
    }

    try {
      $serviceRequestService = app(ServiceRequestService::class);
      $serviceRequestService->assignNurse($serviceRequest->id, $nurseId);
      
      // Status is already updated by the service
      Alert::info('Nurse assigned successfully');
    } catch (\Exception $e) {
      Alert::error('Error assigning nurse: ' . $e->getMessage());
    }
    
    return redirect()->route('platform.service-request.detail', $serviceRequest->id);
  }

  public function assignGP(ServiceRequest $serviceRequest) {
    $doctorId = request()->input('doctor_id');
    
    if (!$doctorId) {
      Alert::error('Please select a GP doctor');
      return redirect()->route('platform.service-request.detail', $serviceRequest->id);
    }

    try {
      $serviceRequestService = app(ServiceRequestService::class);
      $serviceRequestService->assignGP($serviceRequest->id, $doctorId);
      
      // Status is already updated by the service
      Alert::info('GP doctor assigned successfully');
    } catch (\Exception $e) {
      Alert::error('Error assigning GP: ' . $e->getMessage());
    }
    
    return redirect()->route('platform.service-request.detail', $serviceRequest->id);
  }

  private function getCurrentNurseId(): ?int {
    $assignment = $this->serviceRequest->assignedNurse;
    if ($assignment) {
      $nurse = $assignment->assignedTo;
      return $nurse?->id ?? null;
    }
    return null;
  }

  private function getCurrentGPId(): ?int {
    $assignment = $this->serviceRequest->assignedGP;
    if ($assignment) {
      $doctor = $assignment->assignedTo;
      return $doctor?->id ?? null;
    }
    return null;
  }

  public function uploadPatientFile(ServiceRequest $serviceRequest) {
    $user = Auth::user();
    if (!$user) {
      Alert::error('You must be logged in to upload patient files.');
      return redirect()->route('platform.service-request.detail', $serviceRequest->id);
    }

    // Allow admins or assigned doctors
    $isAdmin = $user->hasAccess('platform.systems.users');
    $doctor = Doctor::where('user_id', $user->id)->first();
    $isAssignedDoctor = false;
    
    if ($doctor) {
      $assignment = RequestAssignment::where('service_request_id', $serviceRequest->id)
        ->whereIn('assigned_to_type', ['gp', 'specialist'])
        ->where('assigned_to_id', $doctor->id)
        ->where('status', 'active')
        ->exists();
      $isAssignedDoctor = $assignment;
    }
    
    if (!$isAdmin && !$isAssignedDoctor) {
      Alert::error('You do not have permission to upload patient files.');
      return redirect()->route('platform.service-request.detail', $serviceRequest->id);
    }

    $request = request();
    $validated = $request->validate([
      'patient_file_type' => 'required|string|max:255',
      'patient_file' => 'required|file|max:10240', // 10MB max
      'patient_file_description' => 'nullable|string',
    ]);

    $file = $request->file('patient_file');
    $path = $file->store('patient-files', 'public');

    PatientFile::create([
      'service_request_id' => $serviceRequest->id,
      'file_type' => $validated['patient_file_type'],
      'file_path' => $path,
      'uploaded_by_type' => $isAdmin ? 'admin' : 'doctor',
      'uploaded_by_id' => $isAdmin ? $user->id : ($doctor ? $doctor->id : $user->id),
      'description' => $validated['patient_file_description'] ?? null,
    ]);

    Alert::info('Patient file uploaded successfully');
    return redirect()->route('platform.service-request.detail', $serviceRequest->id);
  }

  public function uploadVitalsReport(ServiceRequest $serviceRequest) {
    $user = Auth::user();
    if (!$user) {
      Alert::error('You must be logged in to upload vitals reports.');
      return redirect()->route('platform.service-request.detail', $serviceRequest->id);
    }

    // Allow admins or nurses
    $isAdmin = $user->hasAccess('platform.systems.users');
    $isNurse = Nurse::where('user_id', $user->id)->exists();
    
    if (!$isAdmin && !$isNurse) {
      Alert::error('You do not have permission to upload vitals reports.');
      return redirect()->route('platform.service-request.detail', $serviceRequest->id);
    }

    // Get nurse ID - use current nurse if available, otherwise find by user_id
    $nurse = $this->currentNurse ?? Nurse::where('user_id', $user->id)->first();
    if (!$nurse && !$isAdmin) {
      Alert::error('Nurse profile not found.');
      return redirect()->route('platform.service-request.detail', $serviceRequest->id);
    }

    $request = request();
    $validated = $request->validate([
      'blood_pressure' => 'nullable|string|max:50',
      'heart_rate' => 'nullable|integer|min:0|max:300',
      'temperature' => 'nullable|numeric|min:0|max:120',
      'oxygen_saturation' => 'nullable|numeric|min:0|max:100',
      'respiratory_rate' => 'nullable|integer|min:0|max:100',
      'weight' => 'nullable|numeric|min:0',
      'height' => 'nullable|numeric|min:0',
      'vitals_notes' => 'nullable|string',
      'vitals_report_file' => 'nullable|file|max:10240', // 10MB max
    ]);

    // Build vitals data array from individual fields
    $vitalsData = [];
    if (!empty($validated['blood_pressure'])) {
      $vitalsData['blood_pressure'] = $validated['blood_pressure'];
    }
    if (!empty($validated['heart_rate'])) {
      $vitalsData['heart_rate'] = (int) $validated['heart_rate'];
    }
    if (!empty($validated['temperature'])) {
      $vitalsData['temperature'] = (float) $validated['temperature'];
    }
    if (!empty($validated['oxygen_saturation'])) {
      $vitalsData['oxygen_saturation'] = (float) $validated['oxygen_saturation'];
    }
    if (!empty($validated['respiratory_rate'])) {
      $vitalsData['respiratory_rate'] = (int) $validated['respiratory_rate'];
    }
    if (!empty($validated['weight'])) {
      $vitalsData['weight'] = (float) $validated['weight'];
    }
    if (!empty($validated['height'])) {
      $vitalsData['height'] = (float) $validated['height'];
    }
    if (!empty($validated['vitals_notes'])) {
      $vitalsData['notes'] = $validated['vitals_notes'];
    }

    // At least one vital sign must be provided
    if (empty($vitalsData)) {
      Alert::error('Please provide at least one vital sign reading.');
      return redirect()->route('platform.service-request.detail', $serviceRequest->id);
    }

    $reportFilePath = null;
    if ($request->hasFile('vitals_report_file')) {
      $file = $request->file('vitals_report_file');
      $reportFilePath = $file->store('vitals-reports', 'public');
    }

    VitalsReport::create([
      'service_request_id' => $serviceRequest->id,
      'nurse_id' => $nurse ? $nurse->id : null, // Allow null for admin uploads
      'vitals_data' => $vitalsData,
      'report_file_path' => $reportFilePath,
      'uploaded_at' => now(),
    ]);

    // Update service request status if needed
    if ($serviceRequest->status === 'nurse_assigned') {
      $serviceRequest->update(['status' => 'vitals_uploaded']);
    }

    Alert::info('Vitals report uploaded successfully');
    return redirect()->route('platform.service-request.detail', $serviceRequest->id);
  }

  public function createReferral(ServiceRequest $serviceRequest) {
    $user = Auth::user();
    if (!$user) {
      Alert::error('You must be logged in to create referrals.');
      return redirect()->route('platform.service-request.detail', $serviceRequest->id);
    }

    // Allow admins or assigned doctors
    $isAdmin = $user->hasAccess('platform.systems.users');
    $doctor = Doctor::where('user_id', $user->id)->first();
    $isAssignedDoctor = false;
    
    if ($doctor) {
      $assignment = RequestAssignment::where('service_request_id', $serviceRequest->id)
        ->whereIn('assigned_to_type', ['gp', 'specialist'])
        ->where('assigned_to_id', $doctor->id)
        ->where('status', 'active')
        ->exists();
      $isAssignedDoctor = $assignment;
    }
    
    if (!$isAdmin && !$isAssignedDoctor) {
      Alert::error('You do not have permission to create referrals.');
      return redirect()->route('platform.service-request.detail', $serviceRequest->id);
    }

    $request = request();
    $validated = $request->validate([
      'referral_specialist_doctor_id' => 'required|exists:doctors,id',
      'referral_notes' => 'nullable|string',
      'referral_patient_name' => 'nullable|string',
      'referral_dob_age' => 'nullable|string',
      'referral_sex' => 'nullable|string',
      'referral_phone' => 'nullable|string',
      'referral_address' => 'nullable|string',
      'referral_emergency_contact' => 'nullable|string',
      'referral_service_name' => 'nullable|string',
      'referral_service_address' => 'nullable|string',
      'referral_service_phone' => 'nullable|string',
      'referral_referring_doctor_name' => 'nullable|string',
      'referral_medical_council_number' => 'nullable|string',
      'referral_referring_doctor_contact' => 'nullable|string',
      'referral_date_of_referral' => 'nullable|string',
      'referral_chief_complaint' => 'nullable|string',
      'referral_history_present_illness' => 'nullable|string',
      'referral_temperature' => 'nullable|string',
      'referral_pulse_rate' => 'nullable|string',
      'referral_respiratory_rate' => 'nullable|string',
      'referral_blood_pressure' => 'nullable|string',
      'referral_oxygen_saturation' => 'nullable|string',
      'referral_random_blood_sugar' => 'nullable|string',
      'referral_examination_findings' => 'nullable|string',
      'referral_provisional_diagnosis' => 'nullable|string',
      'referral_differential_diagnosis_1' => 'nullable|string',
      'referral_differential_diagnosis_2' => 'nullable|string',
      'referral_differential_diagnosis_3' => 'nullable|string',
      'referral_investigations_done' => 'nullable|string',
      'referral_medications' => 'nullable|string',
      'referral_procedures' => 'nullable|string',
      'referral_reason_for_referral' => 'nullable|string',
      'referral_receiving_facility_name' => 'nullable|string',
      'referral_department_specialty' => 'nullable|string',
      'referral_urgency_level' => 'nullable|string|in:routine,urgent,emergency',
      'referral_additional_notes' => 'nullable|string',
    ]);

    // Build referral_data from form fields (strip empty values)
    $formToData = [
      'referral_patient_name' => 'patient_name',
      'referral_dob_age' => 'dob_age',
      'referral_sex' => 'sex',
      'referral_phone' => 'phone',
      'referral_address' => 'address',
      'referral_emergency_contact' => 'emergency_contact',
      'referral_service_name' => 'service_name',
      'referral_service_address' => 'service_address',
      'referral_service_phone' => 'service_phone',
      'referral_referring_doctor_name' => 'referring_doctor_name',
      'referral_medical_council_number' => 'medical_council_number',
      'referral_referring_doctor_contact' => 'referring_doctor_contact',
      'referral_date_of_referral' => 'date_of_referral',
      'referral_chief_complaint' => 'chief_complaint',
      'referral_history_present_illness' => 'history_present_illness',
      'referral_temperature' => 'temperature',
      'referral_pulse_rate' => 'pulse_rate',
      'referral_respiratory_rate' => 'respiratory_rate',
      'referral_blood_pressure' => 'blood_pressure',
      'referral_oxygen_saturation' => 'oxygen_saturation',
      'referral_random_blood_sugar' => 'random_blood_sugar',
      'referral_examination_findings' => 'examination_findings',
      'referral_provisional_diagnosis' => 'provisional_diagnosis',
      'referral_differential_diagnosis_1' => 'differential_diagnosis_1',
      'referral_differential_diagnosis_2' => 'differential_diagnosis_2',
      'referral_differential_diagnosis_3' => 'differential_diagnosis_3',
      'referral_investigations_done' => 'investigations_done',
      'referral_medications' => 'medications',
      'referral_procedures' => 'procedures',
      'referral_reason_for_referral' => 'reason_for_referral',
      'referral_receiving_facility_name' => 'receiving_facility_name',
      'referral_department_specialty' => 'department_specialty',
      'referral_urgency_level' => 'urgency_level',
      'referral_additional_notes' => 'additional_notes',
    ];
    $referralData = [];
    foreach ($formToData as $formKey => $dataKey) {
      $v = $validated[$formKey] ?? null;
      if ($v !== null && trim((string) $v) !== '') {
        $referralData[$dataKey] = trim((string) $v);
      }
    }

    // Determine the GP doctor making the referral
    $gpDoctorId = null;
    if ($doctor && !$isAdmin) {
      // If it's a doctor creating the referral, use that doctor as the GP
      $gpDoctorId = $doctor->id;
    } else {
      // If it's an admin, check if there's an assigned GP doctor
      $gpAssignment = RequestAssignment::where('service_request_id', $serviceRequest->id)
        ->where('assigned_to_type', 'gp')
        ->where('status', 'active')
        ->first();
      if ($gpAssignment) {
        $gpDoctorId = $gpAssignment->assigned_to_id;
      }
    }

    if (!$gpDoctorId) {
      Alert::error('No GP doctor found for this service request. Please assign a GP doctor first.');
      return redirect()->route('platform.service-request.detail', $serviceRequest->id);
    }

    // Check if referral already exists
    $existingReferral = Referral::where('service_request_id', $serviceRequest->id)->first();
    
    if ($existingReferral) {
      // Update existing referral
      $existingReferral->update([
        'specialist_doctor_id' => $validated['referral_specialist_doctor_id'],
        'referral_notes' => $validated['referral_notes'] ?? $existingReferral->referral_notes,
        'referral_data' => empty($referralData) ? $existingReferral->referral_data : $referralData,
      ]);
      Alert::info('Referral updated successfully');
    } else {
      Referral::create([
        'service_request_id' => $serviceRequest->id,
        'gp_doctor_id' => $gpDoctorId,
        'specialist_doctor_id' => $validated['referral_specialist_doctor_id'],
        'referral_notes' => $validated['referral_notes'] ?? null,
        'referral_data' => empty($referralData) ? null : $referralData,
        'status' => 'pending',
      ]);

      // Update service request status
      $serviceRequest->update(['status' => 'specialist_referred']);
      Alert::info('Referral created successfully');
    }

    return redirect()->route('platform.service-request.detail', $serviceRequest->id);
  }

  public function createAdmission(ServiceRequest $serviceRequest) {
    $user = Auth::user();
    if (!$user) {
      Alert::error('You must be logged in to create admissions.');
      return redirect()->route('platform.service-request.detail', $serviceRequest->id);
    }

    // Allow admins or assigned doctors
    $isAdmin = $user->hasAccess('platform.systems.users');
    $doctor = Doctor::where('user_id', $user->id)->first();
    $isAssignedDoctor = false;
    
    if ($doctor) {
      $assignment = RequestAssignment::where('service_request_id', $serviceRequest->id)
        ->whereIn('assigned_to_type', ['gp', 'specialist'])
        ->where('assigned_to_id', $doctor->id)
        ->where('status', 'active')
        ->exists();
      $isAssignedDoctor = $assignment;
    }
    
    if (!$isAdmin && !$isAssignedDoctor) {
      Alert::error('You do not have permission to create admissions.');
      return redirect()->route('platform.service-request.detail', $serviceRequest->id);
    }

    $request = request();
    $validated = $request->validate([
      'admission_specialist_doctor_id' => 'required|exists:doctors,id',
      'admission_date' => 'required|date',
      'expected_days' => 'required|integer|min:1',
    ]);

    // Check if admission already exists
    $existingAdmission = Admission::where('service_request_id', $serviceRequest->id)->first();
    
    if ($existingAdmission) {
      // Update existing admission
      $existingAdmission->update([
        'specialist_doctor_id' => $validated['admission_specialist_doctor_id'],
        'admission_date' => $validated['admission_date'],
        'expected_days' => $validated['expected_days'],
      ]);
      Alert::info('Admission updated successfully');
    } else {
      Admission::create([
        'service_request_id' => $serviceRequest->id,
        'specialist_doctor_id' => $validated['admission_specialist_doctor_id'],
        'admission_date' => $validated['admission_date'],
        'expected_days' => $validated['expected_days'],
        'status' => 'admitted',
      ]);

      // Update service request status
      $serviceRequest->update(['status' => 'admitted']);
      Alert::info('Admission created successfully');
    }

    return redirect()->route('platform.service-request.detail', $serviceRequest->id);
  }
}

