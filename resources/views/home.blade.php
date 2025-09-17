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
            <div class="feature-entry-items text-center">
                <div class="row">
                    <!-- Single Item -->
                    <div class="col-lg-4 single-item">
                        <div class="item">
                            <i class="flaticon-ambulance-1"></i>
                            <h4>Emergency Case</h4>
                            <p>
                                Moment he at on wonder at season little. Six garden result summer set family.
                            </p>
                            <div class="contact">
                                <span>Phone:</span>
                                <p>(+987)98765432</p>
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
                                <li> <span> Mon - Tue :  </span>
                                  <div class="float-right"> 8:00 - 17:30 </div>
                                </li>
                                <li> <span> Wed - Thu :</span>
                                  <div class="float-right"> 10:45 - 15:00 </div>
                                </li>
                                <li> <span> Sun : </span>
                                  <div class="float-right closed"> Closed </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- End Single Item -->
                    <!-- Single Item -->
                    <div class="col-lg-4 single-item">
                        <div class="item">
                            <i class="flaticon-ribbon"></i>
                            <h4>Cancer Care</h4>
                            <p>
                                Moment he at on wonder at season little. Six garden result summer set family.
                            </p>
                            <div class="contact">
                                <span>Phone:</span>
                                <p>(+987)98765432</p>
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
            <div class="department-items text-center">
                <div class="row">
                    <div class="department-area icon-only default-padding bottom-less">
                        <div class="container">
                            <div class="item-box text-center">
                                <div class="department-carousel owl-carousel owl-theme">
                                    <!-- Single Item -->
                                    @foreach ($categories as $category)
                                    <div class="item">
                                        <div class="info">
                                            <h4>
                                                <a href="#">{{ $category->name }}</a>
                                            </h4>
                                            <div class="icon">
                                                @if (!empty($category->image) && file_exists(public_path('uploads/categories/' . $category->image)))
                                                    <img src="{{ $category->image }}" alt="{{ $category->name }}" style="width:60px; height:60px; object-fit:contain;">
                                                @else
                                                    <i class="flaticon-broken-bone"></i>
                                                @endif
                                            </div>
                                            <p>
                                               {{ $category->description }}
                                            </p>
                                            <a class="btn btn-sm circle btn-theme border" href="{{ route('department') }}"><i class="fas fa-angle-right"></i> See All</a>
                                        </div>
                                    </div>
                                    @endforeach
                                    <!-- End Single Item -->
                                    
                                </div>
                            </div>
                        </div>
                    </div>
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
                    <!-- Single Item -->
                    @foreach ($doctors as $doctor)
                    <div class="single-item col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                        <div class="item">
                            <div class="thumb">
                                <img src="{{  $doctor->photo }}" alt="Thumb">
                              
                            </div>
                            <div class="info">
                                <h5>{{ $doctor->name }}</h5>
                                <span>{{ $doctor->specialization }}</span>
                            </div>
                            <a href="{{ route('doctor', $doctor->id) }}" class="btn btn-view-profile">
                                View Profile <i class="fas fa-arrow-right"></i>
                            </a>
                            
                        </div>
                    </div>
                    @endforeach                    
                </div>
            </div>
        </div>
    </div>
    <!-- End Team -->

@endsection
