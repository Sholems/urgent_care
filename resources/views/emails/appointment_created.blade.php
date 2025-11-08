@php
    $patientName = $appointment->user?->user_name ?: 'there';
    $doctorName = $appointment->doctor?->name;
    $doctorLine = $doctorName ? " with {$doctorName}" : '';
@endphp

<p>Hi {{ $patientName }},</p>

<p>Thanks for booking your appointment{{ $doctorLine }} on {{ $appointment->date }} at {{ $appointment->time }}. The doctor will review your request and accept the payment soon. We will update you as soon as the appointment is confirmed.</p>

<p>If you have already shared any documents or payment reference, keep them handy for your visit.</p>

<p>Best regards,<br>{{ config('mail.from.name', 'Urgent Care Team') }}</p>
