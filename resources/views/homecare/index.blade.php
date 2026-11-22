@extends('layouts.app')

@section('title', 'Homecare in Sierra Leone | Lizelsundria Urgent Care')
@section('description', 'Ask about elderly care, nursing and doctor home visits for your family in Sierra Leone.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/homecare.css') }}">
@endpush

@section('content')
    <section class="homecare-hero">
        <div class="container homecare-hero-grid">
            <div>
                <h1>Arrange care at home in Sierra Leone</h1>
                <p>Tell us where your loved one lives and what support they need. We’ll confirm whether we serve their area and discuss availability and the next steps.</p>
                <div class="homecare-actions">
                    <a class="btn btn-md btn-gradient" href="{{ route('homecare.enquiry') }}">Arrange Homecare</a>
                    <a href="{{ route('homecare.families') }}">Arranging care from abroad?</a>
                </div>
            </div>
            <img src="{{ asset('img/trusted-care.jpg') }}" alt="A nurse speaking with an older patient at home">
        </div>
    </section>

    <section class="container homecare-section" aria-labelledby="homecare-services">
        <h2 id="homecare-services">Care options to discuss</h2>
        <div class="homecare-services">
            <article><h3>Elderly care</h3><p>Ask about wellbeing checks, companionship and help with daily activities.</p></article>
            <article><h3>Nursing at home</h3><p>Enquire about scheduled visits or nursing shifts.</p></article>
            <article><h3>Doctor home visits</h3><p>Ask whether a home consultation is available and suitable for your needs.</p></article>
            <article><h3>Post-hospital support</h3><p>Discuss the support your loved one may need after discharge.</p></article>
        </div>
    </section>

    <section class="container homecare-section homecare-next" aria-labelledby="homecare-next">
        <h2 id="homecare-next">What happens after you enquire?</h2>
        <ol>
            <li>Share your contact details, the care location and the service you’re interested in.</li>
            <li>Our team checks coverage, availability and whether an assessment is needed.</li>
            <li>We discuss the proposed care and costs with you before confirming a booking.</li>
        </ol>
        <a class="btn btn-md btn-gradient" href="{{ route('homecare.enquiry') }}">Ask About Availability</a>
    </section>
@endsection
