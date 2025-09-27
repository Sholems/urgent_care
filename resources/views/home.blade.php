@extends('layouts.app')

@section('title', 'Lizelsundria Urgent Care - Home Page')

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
                    <h2 class="wow fadeInDown" data-wow-duration="1s">
                        We are providing <strong>Trusted Medical Services</strong>
                    </h2>
                    <p class="wow fadeInLeft" data-wow-duration="1.5s">
                        Our platform connects patients with experienced and qualified doctors across multiple specialties. 
                        From cardiology and neurology to orthopedics and ENT, you can easily find the right doctor, check 
                        availability, compare consultation fees, and book appointments online. Quality care is now just a 
                        few clicks away.
                    </p>
                    
                  
                </div>
                <div class="col-lg-6 thumb">
                    <img src="{{ asset('img/hero-img.png') }}" alt="Thumb">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Star Feature Entry Area
    ============================================= -->
    <div class="feature-entry-area cols-3 default-padding bottom-less">
        <div class="container">
            @php
                $openingHours = optional($siteSetting)->opening_hours ?? [];
                $defaultHours = [
                    'mon_thu' => '8:00 - 17:30',
                    'fri' => '8:00 - 17:30',
                    'sat' => '10:45 - 15:00',
                    'sun' => 'Closed',
                ];

                $monThuHours = $openingHours['mon_thu']
                    ?? $openingHours['mon_tue']
                    ?? $openingHours['wed_thu']
                    ?? $defaultHours['mon_thu'];
                $friHours = $openingHours['fri'] ?? $defaultHours['fri'];
                $satHours = $openingHours['sat'] ?? $defaultHours['sat'];
                $sunHours = $openingHours['sun'] ?? $defaultHours['sun'];

                $monThuClosedClass = strtolower(trim($monThuHours)) === 'closed' ? 'closed' : '';
                $friClosedClass = strtolower(trim($friHours)) === 'closed' ? 'closed' : '';
                $satClosedClass = strtolower(trim($satHours)) === 'closed' ? 'closed' : '';
                $sunClosedClass = strtolower(trim($sunHours)) === 'closed' ? 'closed' : '';

                $emergencyPhone = optional($siteSetting)->contact_phone
                    ?? optional($siteSetting)->footer_phone
                    ?? '(+987)98765432';
                $cancerCarePhone = optional($siteSetting)->footer_phone
                    ?? optional($siteSetting)->contact_phone
                    ?? '(+987)98765432';
            @endphp

            <div class="feature-entry-items text-center">
                <div class="row">
                    <!-- Single Item -->
                    <div class="col-lg-4 single-item">
                        <div class="item">
                            <i class="flaticon-ambulance-1"></i>
                            <h4>Emergency Case</h4>
                            <p>
                                For urgent cases, contact us immediately for prompt medical attention.
                            </p>
                            <div class="contact">
                                <span>Phone:</span>
                                <p>{{ $emergencyPhone }}</p>
                            </div>
                        </div>
                    </div>
                    <!-- End Single Item -->
                    <!-- Single Item -->
                    <div class="col-lg-4 single-item">
                        <div class="item">
                            <i class="flaticon-time"></i>
                            <h4>Opening Hours</h4>
                            <ul>
                                <li> <span> Mon - Thu :  </span>
                                  <div class="float-right {{ $monThuClosedClass }}"> {{ $monThuHours }} </div>
                                </li>
                                <li> <span> Fri :</span>
                                  <div class="float-right {{ $friClosedClass }}"> {{ $friHours }} </div>
                                </li>
                                <li> <span> Sat :</span>
                                  <div class="float-right {{ $satClosedClass }}"> {{ $satHours }} </div>
                                </li>
                                <li> <span> Sun : </span>
                                  <div class="float-right {{ $sunClosedClass }}"> {{ $sunHours }} </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- End Single Item -->
                    <!-- Single Item -->
                    <div class="col-lg-4 single-item">
                        <div class="item">
                            <i class="flaticon-ribbon"></i>
                            <h4>General Consultation</h4>
                            <p>
                                Book a general consultation for routine check-ups, health advice, or treatment of common illnesses.
                            </p>
                            <div class="contact">
                                <span>Phone:</span>
                                <p>{{ $cancerCarePhone }}</p>
                            </div>
                        </div>
                    </div>
                    <!-- End Single Item -->
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
                        <img src="{{ asset('img/trusted-care.jpg') }}" alt="Thumb">
                        <div class="intro-video">
                            <div class="video">
                                <a href="#" class="popup-youtube relative theme video-play-button item-center">
                                    <i class="fa fa-play"></i>
                                </a>
                            </div>
                            <div class="content">
                                <h5>Let’s see </h5>
                                <p>
                                    If your smile is not becoming to you, then you should be coming.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 info">
                    <h5>Providing Trusted Care</h5>
                    <h2>A Great Place to Work. A Great Place to <strong>Receive Care</strong>. Leading Medicine.</h2>
                    <p class="wow fadeInLeft" data-wow-duration="1.5s">
                        we have been a trusted destination for quality healthcare.  
                        A great place to work, a great place to receive care — our mission is to combine compassion with 
                        leading medical expertise. Our dedicated team of doctors and specialists ensures every patient 
                        receives personalized attention and advanced treatment in a welcoming environment.  
                    </p>
                    
                    <ul class="icon-less">
                        <li>
                            <h5>More Experience</h5>
                        </li>
                        <li>
                            <h5>The right answers?</h5>
                        </li>
                        <li>
                            <h5>Seamless care</h5>
                        </li>
                        <li>
                            <h5>Unparalleled expertise</h5>
                        </li>
                    </ul>
                    <a class="btn btn-md btn-gradient" href="#"><i class="fas fa-angle-right"></i> Choose Best Doctor</a>
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
                                            <img src="{{  $category->image }}" alt="{{ $category->name }}" class="rounded-circle border" style="width:70px; height:70px; object-fit:cover;">
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
                            While mirth large of on front. Ye he greater related adapted proceed entered an. Through it examine express promise no. Past add size game cold girl off how old
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
