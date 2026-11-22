@extends('layouts.app')

@section('title', 'Care for Families Abroad | Lizelsundria Urgent Care')
@section('description', 'Living abroad? Ask Lizelsundria about arranging homecare for a relative in Sierra Leone and learn how enquiries, assessments and quotes work.')

@section('content')
<section class="bg-gray default-padding">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4">
                <h1>Arrange care in Sierra Leone from abroad</h1>
                <p>If your parent or relative needs support at home, tell us where they live and what help you’re looking for. We can discuss available options and whether an assessment is appropriate.</p>
                <a class="btn btn-md btn-gradient" href="{{ route('homecare.enquiry') }}">Start a Homecare Enquiry</a>
            </div>
            <div class="col-lg-6">
                <img class="img-fluid rounded" src="{{ asset('img/trusted-care.jpg') }}" alt="A nurse speaking with an older patient at home">
            </div>
        </div>
    </div>
</section>
<section class="default-padding">
    <div class="container">
        <h2>How to arrange care</h2>
        <div class="row">
            <div class="col-md-4 mb-3">
                <h3>1. Tell us what you need</h3>
                <p>Share your relative’s town or neighbourhood, the type of support you’re looking for and when you’d like it to start.</p>
            </div>
            <div class="col-md-4 mb-3">
                <h3>2. Discuss an assessment</h3>
                <p>Ask about coverage, staffing and whether an assessment is needed. Any proposed care will depend on availability and your relative’s needs.</p>
            </div>
            <div class="col-md-4 mb-3">
                <h3>3. Review the quote</h3>
                <p>Ask for the proposed schedule and costs before confirming care. Discuss who can receive updates, subject to your relative’s consent or appropriate authorisation.</p>
            </div>
        </div>
    </div>
</section>
@endsection
