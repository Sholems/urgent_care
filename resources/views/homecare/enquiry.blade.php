@extends('layouts.app')

@section('title', 'Arrange Homecare | Lizelsundria Urgent Care')
@section('description', 'Contact Lizelsundria about arranging homecare in Sierra Leone.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/homecare.css') }}">
@endpush

@section('content')
    <section class="homecare-section container">
        <div class="homecare-form-panel">
            <a href="{{ route('homecare.index') }}">Homecare services</a>
            <h1>Arrange homecare for your loved one</h1>
            @if (session('homecare_received'))
                <div class="alert alert-success" role="status">
                    <strong>Your enquiry was received.</strong> Our team will use the contact details you supplied to follow up about availability.
                </div>
            @else
                <p>Tell us how to reach you and where care is needed. Please leave out patient names, medical records and diagnoses.</p>
                <p>For an emergency, seek immediate local help. This form is for arranging care.</p>
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <p>Please check the following fields.</p>
                        <ul class="mb-0">
                            @foreach ($errors->getMessages() as $field => $messages)
                                <li><a href="#{{ $field }}">{{ $messages[0] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('homecare.enquiry.store') }}" class="homecare-form">
                    @csrf
                    <div>
                        <label for="name">Your name</label>
                        <input class="form-control" id="name" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="100" required @error('name') aria-invalid="true" @enderror>
                    </div>
                    <div>
                        <label for="email">Your email</label>
                        <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required @error('email') aria-invalid="true" @enderror>
                    </div>
                    <div>
                        <label for="phone">Your phone number with country code (optional)</label>
                        <input class="form-control" id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" maxlength="32" @error('phone') aria-invalid="true" @enderror>
                    </div>
                    <div>
                        <label for="town">Town or neighbourhood where care is needed</label>
                        <input class="form-control" id="town" name="town" value="{{ old('town') }}" maxlength="100" required @error('town') aria-invalid="true" @enderror>
                    </div>
                    <div>
                        <label for="service">Service</label>
                        <select class="form-control" id="service" name="service" required @error('service') aria-invalid="true" @enderror>
                            <option value="">Choose an option</option>
                            @foreach (config('homecare.services') as $key => $label)
                                <option value="{{ $key }}" @selected(old('service') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="preferred_start">Preferred start date (optional)</label>
                        <input class="form-control" id="preferred_start" name="preferred_start" type="date" value="{{ old('preferred_start') }}" min="{{ today()->toDateString() }}" @error('preferred_start') aria-invalid="true" @enderror>
                    </div>
                    <div class="homecare-acknowledgement">
                        <input id="privacy_acknowledged" name="privacy_acknowledged" type="checkbox" value="1" @checked(old('privacy_acknowledged')) required @error('privacy_acknowledged') aria-invalid="true" @enderror>
                        <label for="privacy_acknowledged">I have read the <a href="{{ route('privacy-policy') }}#homecare-enquiries">website enquiry privacy notice</a>.</label>
                    </div>
                    <button class="btn btn-md btn-gradient" type="submit">Send Homecare Enquiry</button>
                </form>
            @endif
        </div>
    </section>
@endsection
