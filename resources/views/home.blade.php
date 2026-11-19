@extends('layouts.app')

@section('title', 'Homecare Enquiries in Sierra Leone | Lizelsundria Urgent Care')

@section('description', 'Ask Lizelsundria about care at home in Sierra Leone, including elderly support, nursing and doctor home visits. Families abroad can enquire online.')

@section('content')
<div class="banner-area auto-height bg-gray fixed-thumb text-default">
    <!-- Fixed Shape -->
    <div class="fixed-shape-left-top">
        <img src="{{ asset('img/shape/1.png') }}" alt="Shape">
    </div>
    <!-- End Fixed Shape -->
    <div class="container">
        <div class="double-items">
            <div class="row align-center">
                <div class="col-lg-6 info">
                    <h1>Arrange care at home in Sierra Leone</h1>
                    <p>Tell us what kind of support you or your loved one needs. Ask about elderly care, nursing at home or a doctor home visit. We’ll discuss what is available in your area.</p>
                    <a class="btn btn-md btn-gradient mr-2 mb-2" href="{{ route('contact', ['enquiry' => 'homecare']) }}#contact">Enquire About Homecare</a>
                    <a class="btn btn-md btn-outline-primary mb-2" href="{{ route('homecare.families') }}">Care for Families Abroad</a>
                </div>
                <div class="col-lg-6 thumb">
                    <img src="{{ asset('img/hero-img.png') }}" alt="Thumb">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Star Feature Entry Area (Modernized Quick Info)
    ============================================= -->
    @php
        $openingHours = optional($siteSetting)->opening_hours ?? [];
        $defaultHours = [
            'mon_sat' => '8:00 am to 04:00 pm',
            'sun_holidays' => 'Appointment/Emergency',
        ];

        $monSatHours = $openingHours['mon_sat'] ?? $defaultHours['mon_sat'];
        $sunHolidaysHours = $openingHours['sun_holidays'] ?? $defaultHours['sun_holidays'];

        $monSatClosedClass = strtolower(trim($monSatHours)) === 'closed' ? 'closed' : '';
        $sunHolidaysClosedClass = strtolower(trim($sunHolidaysHours)) === 'closed' ? 'closed' : '';

        $contactPhone = $siteSetting
            ? $siteSetting->resolvedContactPhone()
            : \App\Support\ContactPhoneFormatter::resolve();

        $phoneList = array_values(array_filter(preg_split('#\s*/\s*#', (string) $contactPhone)));
        $primaryPhone = $phoneList[0] ?? '+232 72 735 530';
        $primaryTel = preg_replace('/[^0-9+]/', '', $primaryPhone);
    @endphp

    <style>
        /* Modern Quick Info Section */
        .modern-quick-info {
            position: relative;
            padding: 50px 0 20px;
            background: transparent;
        }

        .quick-info-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 34px 28px 30px;
            display: flex;
            flex-direction: column;
            position: relative;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            border: 1px solid #e2e8f0;
            transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
            overflow: hidden;
            height: 100%;
        }

        .quick-info-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
        }

        .card-header-badge {
            margin-bottom: 18px;
        }

        .badge-urgent {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fee2e2;
            color: #dc2626;
            font-size: 11.5px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 30px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-hours {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.22);
            color: #ffffff;
            font-size: 11.5px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 30px;
            letter-spacing: 0.5px;
            backdrop-filter: blur(4px);
        }

        .badge-consultation {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #e0f2fe;
            color: #0284c7;
            font-size: 11.5px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 30px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-icon-wrapper {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 28px;
            transition: transform 0.3s ease;
        }

        .quick-info-card:hover .card-icon-wrapper {
            transform: scale(1.08);
        }

        .card-icon-wrapper.red-accent {
            background: #fef2f2;
            color: #ef4444;
        }

        .card-icon-wrapper.blue-accent {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        .card-icon-wrapper.teal-accent {
            background: #f0fdf4;
            color: #10b981;
        }

        .quick-info-card .card-title {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
        }

        .quick-info-card .card-desc {
            color: #64748b;
            font-size: 14.5px;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        /* Emergency Card Specifics */
        .emergency-card {
            border-top: 4px solid #ef4444;
        }

        .btn-call-emergency {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff !important;
            font-weight: 700;
            font-size: 14.5px;
            padding: 13px 18px;
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.3);
            transition: all 0.25s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-call-emergency:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.45);
            transform: translateY(-2px);
            color: #ffffff !important;
        }

        .alt-phone-pill {
            display: inline-block;
            background: #f1f5f9;
            color: #475569;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .alt-phone-pill:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        /* Hours Card Specifics */
        .hours-card {
            background: linear-gradient(145deg, #0284c7 0%, #0369a1 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
        }

        .hours-schedule {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 20px;
            backdrop-filter: blur(8px);
        }

        .schedule-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            font-size: 13.5px;
        }

        .schedule-row:last-child {
            border-bottom: none;
        }

        .schedule-day {
            font-weight: 600;
            color: rgba(255, 255, 255, 0.95);
        }

        .schedule-time {
            font-weight: 700;
            color: #ffffff;
            background: rgba(255, 255, 255, 0.22);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12.5px;
        }

        .schedule-time.closed {
            background: rgba(239, 68, 68, 0.4);
            color: #fee2e2;
        }

        .hours-note {
            line-height: 1.5;
            margin-top: auto;
            color: rgba(255, 255, 255, 0.85);
            font-size: 13px;
        }

        /* Consultation Card Specifics */
        .consultation-card {
            border-top: 4px solid #0284c7;
        }

        .btn-book-consult {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff !important;
            font-weight: 700;
            font-size: 14.5px;
            padding: 13px 18px;
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.28);
            transition: all 0.25s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-book-consult:hover {
            background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.45);
            transform: translateY(-2px);
            color: #ffffff !important;
        }

        .consultation-phone a {
            color: #0284c7 !important;
            font-weight: 700;
            text-decoration: none;
        }

        .consultation-phone a:hover {
            text-decoration: underline;
        }
    </style>

    <div class="feature-entry-area modern-quick-info bottom-less">
        <div class="container">
            <div class="row align-items-stretch">
                <!-- Single Item 1: Emergency -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="quick-info-card emergency-card">
                        <div class="card-header-badge">
                            <span class="badge-urgent"><i class="fas fa-ambulance"></i> Urgent Care</span>
                        </div>
                        <div class="card-icon-wrapper red-accent">
                            <i class="flaticon-ambulance-1"></i>
                        </div>
                        <h4 class="card-title">Emergency Case</h4>
                        <p class="card-desc">
                            For urgent cases and emergency home visits, contact our response team immediately for prompt medical attention.
                        </p>
                        <div class="mt-auto">
                            <a href="tel:{{ $primaryTel }}" class="btn btn-call-emergency w-100 mb-2">
                                <i class="fas fa-phone-alt me-2"></i> Call: {{ $primaryPhone }}
                            </a>
                            @if(count($phoneList) > 1)
                                <div class="text-center mt-2">
                                    <span class="d-block small text-muted mb-1">Additional Lines:</span>
                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                        @foreach(array_slice($phoneList, 1) as $alt)
                                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $alt) }}" class="alt-phone-pill me-1 mb-1">
                                                {{ $alt }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Single Item 2: Opening Hours -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="quick-info-card hours-card">
                        <div class="card-header-badge">
                            <span class="badge-hours"><i class="fas fa-clock"></i> Clinical Hours</span>
                        </div>
                        <div class="card-icon-wrapper blue-accent">
                            <i class="flaticon-time"></i>
                        </div>
                        <h4 class="card-title text-white">Opening Hours</h4>
                        <div class="hours-schedule">
                            <div class="schedule-row">
                                <span class="schedule-day">Mon &ndash; Sat:</span>
                                <span class="schedule-time {{ $monSatClosedClass }}">{{ $monSatHours }}</span>
                            </div>
                            <div class="schedule-row">
                                <span class="schedule-day">Sun &amp; Holidays:</span>
                                <span class="schedule-time {{ $sunHolidaysClosedClass }}">{{ $sunHolidaysHours }}</span>
                            </div>
                        </div>
                        <div class="hours-note">
                            <i class="fas fa-shield-alt me-1"></i> Emergency home visits &amp; telephone consultations available on-call.
                        </div>
                    </div>
                </div>

                <!-- Single Item 3: General Consultation -->
                <div class="col-lg-4 col-md-12 mb-4">
                    <div class="quick-info-card consultation-card">
                        <div class="card-header-badge">
                            <span class="badge-consultation"><i class="fas fa-user-md"></i> Home &amp; Clinic</span>
                        </div>
                        <div class="card-icon-wrapper teal-accent">
                            <i class="flaticon-ribbon"></i>
                        </div>
                        <h4 class="card-title">General Consultation</h4>
                        <p class="card-desc">
                            Book a general consultation for routine check-ups, health advice, or home care treatment in Sierra Leone.
                        </p>
                        <div class="mt-auto">
                            <a href="{{ route('contact', ['enquiry' => 'homecare']) }}#contact" class="btn btn-book-consult w-100 mb-2">
                                <i class="fas fa-calendar-check me-2"></i> Book Consultation
                            </a>
                            <div class="consultation-phone text-center small text-muted mt-2">
                                Direct Phone: <a href="tel:{{ $primaryTel }}">{{ $primaryPhone }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Start About
    ============================================= -->
    <div class="about-area bg-gray relative default-padding">

        <div class="container">
            <div class="row">

                <div class="col-lg-6 thumb">
                    <div class="thumb-box">
                        <img src="{{ asset('img/trusted-care.jpg') }}" alt="A nurse speaking with an older patient at home" loading="lazy">
                    </div>
                </div>

                <div class="col-lg-6 info">
                    <h5>Homecare Enquiries</h5>
                    <h2>Explore care options for your family</h2>
                    <ul class="icon-less">
                        <li><h5>Elderly care</h5><p>Ask about companionship, wellbeing checks and help with daily activities.</p></li>
                        <li><h5>Nursing at home</h5><p>Ask about scheduled nursing visits, shifts or support after a hospital stay.</p></li>
                        <li><h5>Doctor home visits</h5><p>Ask whether a home visit is available and appropriate for your situation.</p></li>
                    </ul>
                    <p>Options depend on location, staffing and an assessment of care needs.</p>
                    <a class="btn btn-md btn-gradient" href="{{ route('contact', ['enquiry' => 'homecare']) }}#contact">Ask About Availability</a>
                </div>
            </div>
        </div>
    </div>
    <!-- End About -->

    <!-- Start Services 
    ============================================= -->
    <div class="department-area solid-thumb carousel-shadow default-padding bottom-less">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <div class="site-heading text-center">
                        <h4>Services</h4>
                        <h2>Our Department</h2>
                        <p class="wow fadeInLeft" data-wow-duration="1.5s">
                            We provide a wide range of medical specialties to ensure complete healthcare support for you and your family. 
                            From <strong>Cardiology</strong> for heart health, <strong>Neurology</strong> for brain and nerves, and 
                            <strong>Orthopedics</strong> for bones and joints, to <strong>Pediatrics</strong> for children’s care and 
                            <strong>Dermatology</strong> for skin treatments – our platform connects you with trusted doctors across all major fields. 
                            Whatever your health need, you’ll find the right specialist here.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="department-items text-center py-4">
                <div class="row justify-content-center">
                    @foreach ($categories as $category)
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                            <div class="card h-100 shadow-sm border-0 rounded-lg">
                                <div class="card-body d-flex flex-column align-items-center">
                                    <div class="mb-3">
                                        @if (!empty($category->image))
                                            <img src="{{ asset($category->image) }}" alt="{{ $category->name }}" class="rounded-circle border" style="width:70px; height:70px; object-fit:contain; padding:8px; background:#fff;">
                                        @else
                                            <span class="d-inline-block bg-light rounded-circle p-3">
                                                <i class="flaticon-broken-bone text-theme" style="font-size:40px;"></i>
                                            </span>
                                        @endif
                                    </div>
                                    <h5 class="mb-2 font-weight-bold">
                                        <a href="#" class="text-dark">{{ $category->name }}</a>
                                    </h5>
                                    <p class="text-muted small mb-0">
                                        {{ $category->description }}
                                    </p>
                                </div>
                                <div class="card-footer bg-transparent border-0 text-center">
                                    <a class="btn btn-sm btn-gradient rounded-pill px-3" href="{{ route('department') }}">
                                        See All <i class="fas fa-angle-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <!-- End Services -->

    <!-- Start Choose Us 
    ============================================= -->
    <div class="choose-us-area">
        <div class="row">
            <div class="col-lg-6 thumb bg-cover" style="background-image: url({{ asset('img/banner-img.jpg') }});"></div>
            <div class="col-lg-6 info">
                <div class="info-box">
                    <h5>At Our Clinic</h5>
                    <h2>Our Doctors <br> Specialize in you.</h2>
                    <p>
                        Our doctors are highly qualified, compassionate, and dedicated to providing personalized care. 
                        Each physician brings years of medical expertise and specialized training to ensure accurate diagnosis 
                        and effective treatment
                    </p>
                    <p>
                        Whether you need routine check-ups, expert consultation, or advanced procedures, 
    our doctors are committed to supporting your health and well-being every step of the way.
                    </p>
                    <a class="btn btn-md btn-gradient" href="{{ route('doctors') }}"><i class="fas fa-angle-right"></i> Doctor Lists</a>
                </div>
            </div>
        </div>
    </div>
    <!-- End Choose Us -->

    <!-- Start Team 
    ============================================= -->
    <div class="team-default-area bg-gray default-padding bottom-less">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <div class="site-heading text-center">
                        <h4>Doctors</h4>
                        <h2>Meet our Experts</h2>
                        <p>
                            Our experienced and diverse team of doctors is dedicated to providing expert care across a wide range of medical specialties. Meet the professionals who are committed to supporting your health and well-being every step of the way.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="team-items">
            <div class="row justify-content-center">
                @foreach ($doctors as $doctor)
                <div class="single-item col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                    <div class="card h-100 shadow-lg border-0 rounded-xl overflow-hidden position-relative">
                    <div class="bg-gradient position-absolute w-100" style="height: 60px; top: 0; left: 0; opacity: 0.12;"></div>
                    <div class="card-body d-flex flex-column align-items-center pt-4 pb-3">
                        <div class="mb-3 position-relative">
                        <img 
                            src="{{ $doctor->photo }}" 
                            alt="{{ $doctor->name }}" 
                            class="rounded-circle border border-3 border-theme shadow"
                            style="width:100px; height:100px; object-fit:cover;"
                        >
                      
                        </div>
                        <h5 class="mb-1 font-weight-bold text-center text-theme">{{ $doctor->name }}</h5>
                        <span class="badge bg-gradient text-white mb-2 px-3 py-1 rounded-pill" style="font-size: 0.95em;">
                        {{ $doctor->specialization }}
                        </span>
                        <p class="text-muted small mb-2 text-center">
                        {{ $doctor->experience ? 'With ' . $doctor->experience . ' years of experience.' : 'Experienced and caring medical professional.' }}
                        </p>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-center pb-3">
                        <a href="{{ route('doctor', $doctor->id) }}" class="btn btn-sm btn-gradient rounded-pill px-4 shadow">
                        View Profile <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    </div>
                </div>
                @endforeach                    
            </div>
            </div>
        </div>
    </div>
    <!-- End Team -->

@endsection
