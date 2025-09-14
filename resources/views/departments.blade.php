@extends('layouts.app')

@section('title', 'Lizelsundria Urgent Care - Departments')

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
                    <h1>Departments</h1>
                    <ul class="breadcrumb">
                        <li><a href="#"><i class="fas fa-home"></i> Home</a></li>
                        <li class="active">Departments</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumb -->


    <div class="department-area icon-only bg-gray carousel-shadow default-padding bottom-less">
        <div class="container">
            <div class="item-box text-center">
                <div class="row">
                    @foreach ($categories as $category)
                    <div class="single-item col-lg-4 col-md-6">
                        <div class="item">
                            <div class="info">
                                <h4>
                                    <a href="#">{{ $category->name }}</a>
                                </h4>
                                <div class="icon">
                                    @if (!empty($category->photo))
                                        <img src="{{ $category->photo }}" alt="{{ $category->name }}" style="width:60px; height:60px; object-fit:contain;">
                                    @else
                                        <i class="flaticon-broken-bone"></i>
                                    @endif
                                </div>
                                <p>
                                   {{ $category->description }}
                                </p>
                                <a href="{{ route('doctors.category', $category->name) }}" class="btn btn-view-profile">
                                    View Doctors <i class="fas fa-arrow-right"></i>
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

    <div class="fun-factor-area half-bg-light-bottom bg-gray default-padding-bottom">
        <div class="container">
            <div class="fun-fact-items bg-gradient text-light text-center">
                <div class="row">
                    <div class="col-lg-3 col-md-6 item">
                        <div class="fun-fact">
                            <div class="timer" data-to="230" data-speed="5000"></div>
                            <span class="medium">Satisfied Patients</span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 item">
                        <div class="fun-fact">
                            <div class="timer" data-to="89" data-speed="5000"></div>
                            <span class="medium">Regular Doctors</span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 item">
                        <div class="fun-fact">
                            <div class="timer" data-to="50" data-speed="5000"></div>
                            <span class="medium">Departments</span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 item">
                        <div class="fun-fact">
                            <div class="timer" data-to="2348" data-speed="5000"></div>
                            <span class="medium">Servant</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
