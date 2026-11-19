@php
  $referral = isset($referral) ? $referral : null;
  $data = $referral?->referral_data ?? [];
  if (!is_array($data)) {
    $data = [];
  }
  $labels = [
    'patient_name' => 'Full Name', 'dob_age' => 'DOB/Age', 'sex' => 'Sex', 'phone' => 'Phone', 'address' => 'Address', 'emergency_contact' => 'Emergency contact',
    'service_name' => 'Service', 'service_address' => 'Service address', 'service_phone' => 'Service phone',
    'referring_doctor_name' => 'Referring doctor', 'medical_council_number' => 'Medical Council No', 'referring_doctor_contact' => 'Contact', 'date_of_referral' => 'Date of referral',
    'chief_complaint' => 'Chief complaint', 'history_present_illness' => 'History of present illness',
    'temperature' => 'Temp', 'pulse_rate' => 'Pulse', 'respiratory_rate' => 'Resp rate', 'blood_pressure' => 'BP', 'oxygen_saturation' => 'SpO2', 'random_blood_sugar' => 'RBS',
    'examination_findings' => 'Examination', 'provisional_diagnosis' => 'Provisional diagnosis',
    'differential_diagnosis_1' => 'Differential 1', 'differential_diagnosis_2' => 'Differential 2', 'differential_diagnosis_3' => 'Differential 3',
    'investigations_done' => 'Investigations', 'medications' => 'Medications', 'procedures' => 'Procedures',
    'reason_for_referral' => 'Reason for referral', 'receiving_facility_name' => 'Receiving facility', 'department_specialty' => 'Dept/Specialty', 'urgency_level' => 'Urgency', 'additional_notes' => 'Additional notes',
  ];
  $sections = [
    'Patient' => ['patient_name', 'dob_age', 'sex', 'phone', 'address', 'emergency_contact'],
    'Referring' => ['service_name', 'service_address', 'service_phone', 'referring_doctor_name', 'medical_council_number', 'referring_doctor_contact', 'date_of_referral'],
    'Clinical' => ['chief_complaint', 'history_present_illness', 'temperature', 'pulse_rate', 'respiratory_rate', 'blood_pressure', 'oxygen_saturation', 'random_blood_sugar', 'examination_findings'],
    'Diagnosis' => ['provisional_diagnosis', 'differential_diagnosis_1', 'differential_diagnosis_2', 'differential_diagnosis_3'],
    'Treatment & reason' => ['investigations_done', 'medications', 'procedures', 'reason_for_referral', 'receiving_facility_name', 'department_specialty', 'urgency_level', 'additional_notes'],
  ];
@endphp
<div class="p-3" style="max-height: 70vh; overflow-y: auto;">
  @if(!$referral)
    <p class="text-danger mb-0">Referral not found.</p>
  @elseif(empty($data))
    <p class="text-muted mb-0">No referral form data.</p>
  @else
    @foreach($sections as $sectionTitle => $keys)
      @php $rows = []; @endphp
      @foreach($keys as $key)
        @if(!empty($data[$key]) && trim((string)$data[$key]) !== '')
          @php $rows[] = ['label' => $labels[$key] ?? $key, 'value' => $data[$key]]; @endphp
        @endif
      @endforeach
      @if(count($rows) > 0)
        <div class="mb-3">
          <strong class="text-primary d-block mb-2">{{ $sectionTitle }}</strong>
          @foreach($rows as $row)
            <div class="mb-1"><strong>{{ $row['label'] }}:</strong> {{ e($row['value']) }}</div>
          @endforeach
        </div>
      @endif
    @endforeach
    @if(!empty($referral?->referral_notes))
      <div class="mt-3 pt-3 border-top">
        <strong class="text-primary d-block mb-2">Referral notes</strong>
        <div>{{ e($referral->referral_notes) }}</div>
      </div>
    @endif
  @endif
</div>
