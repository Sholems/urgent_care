@php
    $name = $user->user_name ?: 'there';
@endphp

<p>Hi {{ $name }},</p>

<p>Welcome to Lizelsundria Urgent Care! Your account is all set up, and you can now book appointments, manage your medical details, and stay on top of your care.</p>

<p>If you ever need assistance, just reply to this email and our support team will be happy to help.</p>

<p>Best regards,<br>{{ config('mail.from.name', 'Lizelsundria Urgent Care') }}</p>
