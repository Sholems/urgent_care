@extends('layouts.app')

@section('title', 'Homecare Enquiries in Sierra Leone | Lizelsundria Urgent Care')

@section('description', 'Ask Lizelsundria about care at home in Sierra Leone, including elderly support, nursing and doctor home visits. Families abroad can enquire online.')

@section('content')
<div class="banner-area auto-height modern-hero-section">
    <!-- Ambient Background Glow & Shapes -->
    <div class="hero-ambient-glow"></div>
    <div class="hero-ambient-glow-2"></div>
    <div class="fixed-shape-left-top">
        <img src="{{ asset('img/shape/1.png') }}" alt="Shape">
    </div>

    <div class="container">
        <div class="row modern-hero-row">
            <!-- Left Info Column -->
            <div class="col-md-6 col-lg-6 hero-info-col">
                <div class="hero-pill-badge">
                    <span class="hero-pulse-dot"></span>
                    <i class="fas fa-heartbeat"></i> Compassionate In-Home Care
                </div>

                <h1 class="hero-headline">
                    Arrange care at home in <span class="hero-gradient-text">Sierra Leone</span>
                </h1>

                <p class="hero-description">
                    Tell us what kind of support you or your loved one needs. Ask about elderly care, nursing at home or a doctor home visit. We’ll discuss what is available in your area.
                </p>

                <div class="hero-actions">
                    <a class="btn-hero-primary" href="{{ route('contact', ['enquiry' => 'homecare']) }}#contact">
                        <span>Enquire About Homecare</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <a class="btn-hero-secondary" href="{{ route('homecare.families') }}">
                        <i class="fas fa-globe"></i>
                        <span>Care for Families Abroad</span>
                    </a>
                </div>

                <!-- Trust Micro-Highlights (strictly from original copy) -->
                <div class="hero-trust-list">
                    <div class="trust-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Elderly Care</span>
                    </div>
                    <div class="trust-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Nursing at Home</span>
                    </div>
                    <div class="trust-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Doctor Home Visits</span>
                    </div>
                </div>
            </div>

            <!-- Right Visual Column -->
            <div class="col-md-6 col-lg-6 hero-thumb-col text-center">
                <div class="hero-image-wrapper">
                    <div class="hero-image-backdrop"></div>
                    <img src="{{ asset('img/hero-img.png') }}" alt="Arrange care at home in Sierra Leone" class="hero-main-img img-fluid">
                    
                    <!-- Floating Badge 1: Doctor Visits -->
                    <div class="floating-badge badge-top-right">
                        <div class="floating-icon icon-doctor">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="floating-text">
                            <strong>Doctor On-Call</strong>
                            <span>Home Visits Available</span>
                        </div>
                    </div>

                    <!-- Floating Badge 2: Families & Loved Ones -->
                    <div class="floating-badge badge-bottom-left">
                        <div class="floating-icon icon-care">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="floating-text">
                            <strong>Trusted Homecare</strong>
                            <span>Care for Loved Ones</span>
                        </div>
                    </div>
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
        /* Modern Hero Section Styles */
        .modern-hero-section {
            position: relative;
            overflow: hidden;
            padding: 80px 0 65px;
            background: linear-gradient(145deg, #f8fafc 0%, #f0f7ff 50%, #f8fafc 100%);
            border-bottom: 1px solid #edf2f7;
        }

        .hero-ambient-glow {
            position: absolute;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.14) 0%, rgba(37, 99, 235, 0.05) 50%, transparent 75%);
            filter: blur(55px);
            top: -100px;
            right: 8%;
            pointer-events: none;
            z-index: 1;
        }

        .hero-ambient-glow-2 {
            position: absolute;
            width: 380px;
            height: 380px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.08) 0%, transparent 70%);
            filter: blur(50px);
            bottom: -50px;
            left: 5%;
            pointer-events: none;
            z-index: 1;
        }

        .modern-hero-row {
            position: relative;
            z-index: 2;
        }

        .hero-info-col {
            padding-right: 35px;
        }

        .hero-pill-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            background: #e0f2fe;
            border: 1px solid rgba(2, 132, 199, 0.25);
            border-radius: 50px;
            color: #0284c7;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 20px;
            letter-spacing: 0.3px;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.08);
        }

        .hero-pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: heroDotPulse 2s infinite;
        }

        @keyframes heroDotPulse {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .hero-headline {
            font-size: 44px;
            font-weight: 800;
            line-height: 1.18;
            color: #0f172a;
            letter-spacing: -1px;
            margin-bottom: 20px;
        }

        .hero-gradient-text {
            background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-description {
            font-size: 17px;
            line-height: 1.7;
            color: #475569;
            margin-bottom: 30px;
            max-width: 530px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 14px;
            margin-bottom: 32px;
        }

        .btn-hero-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 13px 28px;
            background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            color: #ffffff !important;
            border-radius: 50px;
            font-weight: 700;
            font-size: 15px;
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.28);
            transition: all 0.3s ease;
            text-decoration: none !important;
            border: none;
        }

        .btn-hero-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(37, 99, 235, 0.38);
            color: #ffffff !important;
        }

        .btn-hero-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 26px;
            background: #ffffff;
            color: #1e293b !important;
            border: 1.5px solid #cbd5e1;
            border-radius: 50px;
            font-weight: 600;
            font-size: 15px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
            transition: all 0.3s ease;
            text-decoration: none !important;
        }

        .btn-hero-secondary:hover {
            border-color: #0284c7;
            color: #0284c7 !important;
            background: #f0f9ff;
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(2, 132, 199, 0.12);
        }

        .hero-trust-list {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 20px;
            padding-top: 18px;
            border-top: 1px solid #e2e8f0;
        }

        .trust-item {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 13.5px;
            font-weight: 600;
            color: #475569;
        }

        .trust-item i {
            color: #10b981;
            font-size: 15px;
        }

        /* Hero Image & Floating Cards */
        .hero-image-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        .hero-image-backdrop {
            position: absolute;
            top: 10%;
            left: 8%;
            width: 84%;
            height: 84%;
            background: linear-gradient(135deg, rgba(224, 242, 254, 0.8) 0%, rgba(219, 234, 254, 0.5) 100%);
            border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
            filter: blur(35px);
            z-index: 1;
            pointer-events: none;
        }

        .hero-main-img {
            position: relative;
            z-index: 2;
            max-height: 460px;
            width: auto;
            filter: drop-shadow(0 15px 30px rgba(15, 23, 42, 0.12));
            transition: transform 0.4s ease;
        }

        .hero-image-wrapper:hover .hero-main-img {
            transform: scale(1.02);
        }

        .floating-badge {
            position: absolute;
            z-index: 3;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 16px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
            text-align: left;
            animation: heroCardFloat 5s ease-in-out infinite;
        }

        .badge-top-right {
            top: 8%;
            right: -2%;
        }

        .badge-bottom-left {
            bottom: 6%;
            left: -4%;
            animation-delay: -2.5s;
        }

        @keyframes heroCardFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .floating-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .icon-doctor {
            background: #e0f2fe;
            color: #0284c7;
        }

        .icon-care {
            background: #fef2f2;
            color: #ef4444;
        }

        .floating-text strong {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.25;
            margin-bottom: 2px;
        }

        .floating-text span {
            font-size: 11px;
            color: #64748b;
            font-weight: 500;
        }

        @media (max-width: 991px) {
            .modern-hero-section {
                padding: 60px 0 50px;
                text-align: center;
            }
            .hero-info-col {
                padding-right: 15px;
                margin-bottom: 40px;
            }
            .hero-headline {
                font-size: 32px;
            }
            .hero-description {
                margin-left: auto;
                margin-right: auto;
            }
            .hero-actions {
                justify-content: center;
            }
            .hero-trust-list {
                justify-content: center;
            }
            .badge-top-right {
                right: 4%;
            }
            .badge-bottom-left {
                left: 2%;
            }
        }

        @media (max-width: 576px) {
            .hero-headline {
                font-size: 26px;
            }
            .floating-badge {
                padding: 8px 12px;
            }
            .floating-icon {
                width: 34px;
                height: 34px;
                font-size: 14px;
            }
            .floating-text strong {
                font-size: 11.5px;
            }
            .floating-text span {
                font-size: 10px;
            }
        }

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
