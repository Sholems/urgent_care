@php
    $patientName = $appointment->user?->user_name ?: 'there';
    $doctorName = $appointment->doctor?->name;
    $doctorLine = $doctorName ? " with Dr. {$doctorName}" : '';
@endphp

<p>Hi {{ $patientName }},</p>

@if ($status === 'accepted')
    <p>Your appointment{{ $doctorLine }} on {{ $appointment->date }} at {{ $appointment->time }} has been accepted. We look forward to seeing you then.</p>
@else
    <p>We are sorry to let you know that your appointment{{ $doctorLine }} on {{ $appointment->date }} at {{ $appointment->time }} has been declined. If you have made a payment you will get a refund in 7 days.</p>
@endif

<p>If you have any questions just reply to this email.</p>

<p>Best regards,<br>{{ config('mail.from.name', 'Urgent Care Team') }}</p>
