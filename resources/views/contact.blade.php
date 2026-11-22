@extends('layouts.app')

@section('title', 'Lizelsundria Urgent Care - Contact')

@section('content')
    <!-- Start Breadcrumb
        ============================================= -->
    <div class="breadcrumb-area bg-gradient text-center">
        <!-- Fixed BG -->
        <div class="fixed-bg" style="background-image: url({{ asset('img/shape/9.png') }});"></div>
        <!-- Fixed BG -->
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <h1>Contact Us</h1>
                    <ul class="breadcrumb">
                        <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
                        <li class="active">Contact</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Start Contact Area
        ============================================= -->
    @php
        $contactPhone = $siteSetting
            ? $siteSetting->resolvedContactPhone()
            : \App\Support\ContactPhoneFormatter::resolve();
        $contactEmail = optional($siteSetting)->contact_email ?? optional($siteSetting)->footer_email ?? 'info@lizelsundria.com';
    @endphp

    <div id="contact" class="contact-us-area default-padding">
        <div class="container">
            <div class="contact-items">
                <div class="row">

                    <div class="col-lg-7 wow address-box fadeInUp bg-cover"
                        style="background-image: url({{ asset('img/contact-us.jpg') }});">
                        <div class="address-info">
                            <ul>
                                <li>
                                    <h5><i class="flaticon-call"></i> Phone</h5>
                                    <span>
                                        @foreach (explode(' / ', $contactPhone) as $phone)
                                            @unless ($loop->first) / @endunless
                                            <a class="text-white" href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}">{{ $phone }}</a>
                                        @endforeach
                                    </span>
                                </li>
                                <li>
                                    <h5><i class="flaticon-email"></i> Email</h5>
                                    <span><a class="text-white" href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-5 wow fadeInLeft contact-form-box">
                        <h2>Need help? <strong>Ask your questions</strong></h2>
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                        <p>Looking for care at home? <a href="{{ route('homecare.enquiry') }}">Use the homecare enquiry form</a>.</p>
                        <form id="contactForm" action="{{ route('contact.submit') }}" method="POST" >
                            @csrf
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <input class="form-control" id="name" name="name" placeholder="Name"
                                            type="text" required>
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <input class="form-control" id="email" name="email" placeholder="Email*"
                                            type="email" required>
                                        @error('email')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <input class="form-control" id="phone" name="phone" placeholder="Phone"
                                            type="text">
                                        @error('phone')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group comments">
                                        <textarea class="form-control" id="comments" name="comments" aria-label="Your enquiry" placeholder="How can we help?" required></textarea>
                                        @error('comments')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <button type="submit" class="btn btn-primary">
                                        Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div aria-live="polite" aria-atomic="true" class="position-relative">
                            <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- End Contact -->

    <!-- Start Google Maps
        ============================================= -->
    <div class="maps-area">
        <div class="google-maps">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m10!1m8!1m3!1d14767.262289338461!2d70.79414485000001!3d22.284975!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sin!4v1424308883981"></iframe>
        </div>
    </div>
    <!-- End Google Maps -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#contactForm').on('submit', function(e) {
        e.preventDefault(); // prevent normal submit

        let form = $(this);
        let url = form.attr('action');
        let formData = form.serialize();

        $.ajax({
            type: "POST",
            url: url,
            data: formData,
            success: function(response) {
                showToast('success', response.success);
                form[0].reset();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let errorMessages = [];
                    $.each(errors, function(key, value) {
                        errorMessages.push(value[0]);
                    });
                    showToast('danger', errorMessages.join('<br>'));
                } else {
                    showToast('danger', 'Something went wrong. Please try again.');
                }
            }
        });
    });

    function showToast(type, message) {
    let toastId = 'toast-' + Date.now();

    // Determine Bootstrap classes
    let bgClass = type === 'success' ? 'bg-success text-white' : 'bg-danger text-white';

    let toastHtml = `
        <div id="${toastId}" class="toast align-items-center ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    $('#toast-container').append(toastHtml);

    let toastEl = new bootstrap.Toast(document.getElementById(toastId), { delay: 5000 });
    toastEl.show();

    // Remove toast from DOM after hidden
    document.getElementById(toastId).addEventListener('hidden.bs.toast', function () {
        $(this).remove();
    });
}

});
</script>


@endsection
