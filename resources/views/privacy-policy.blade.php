@extends('layouts.app')

@section('title')
Lizelsundria Urgent Care - Privacy Policy
@endsection

@section('content')
    <!-- Start Breadcrumb -->
    <div class="breadcrumb-area bg-gradient text-center">
        <div class="fixed-bg" style="background-image: url({{ asset('img/shape/9.png') }});"></div>
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <h1>Privacy Policy</h1>
                    <ul class="breadcrumb">
                        <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
                        <li class="active">Privacy Policy</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <div class="default-padding bottom-less">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 offset-lg-1">
                    <div class="bg-gray rounded p-4 p-md-5">
                        <p class="text-muted mb-4">Last updated: {{ now()->format('F j, Y') }}</p>

                        <h5 class="mb-2">1. Information We Collect</h5>
                        <p class="mb-4">We collect information you provide when booking appointments, contacting us, or using our services. This may include your name, email, phone number, and health-related details necessary for your care.</p>

                        <h5 class="mb-2">2. How We Use Your Information</h5>
                        <p class="mb-4">We use your information to schedule and manage appointments, communicate with you about your care, and improve our services. We do not sell your personal information.</p>

                        <h5 class="mb-2">3. Information Sharing</h5>
                        <p class="mb-4">We may share your information only with healthcare providers involved in your care, or when required by law. We do not share your data with third parties for marketing purposes.</p>

                        <h5 class="mb-2">4. Data Security</h5>
                        <p class="mb-4">We take reasonable steps to protect your personal information using secure systems and practices. Access to your data is limited to authorized personnel.</p>

                        <h5 class="mb-2">5. Your Rights</h5>
                        <p class="mb-4">You may request access to, correction of, or deletion of your personal data. Contact us using the details in our footer to make such a request.</p>

                        <h5 class="mb-2">6. Contact Us</h5>
                        <p class="mb-0">If you have questions about this Privacy Policy, please contact us at {{ optional($siteSetting)->footer_email ?? 'support@lizelsundria.com' }} or call {{ $siteSetting ? $siteSetting->resolvedContactPhone() : \App\Support\ContactPhoneFormatter::resolve() }}.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
