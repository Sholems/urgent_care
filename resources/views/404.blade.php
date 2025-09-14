@extends('layouts.app')

@section('title', 'Lizelsundria Urgent Care - 404 Page Not Found')

@section('content')
<div class="error-page-area default-padding">
    <div class="container">
        <div class="row align-center text-center">
            <div class="col-lg-8 offset-lg-2">
                <div class="error-box">
                    <h1>4<i class="fas fa-sad-tear"></i>4</h1>
                    <h2>Sorry Page Was Not Found!</h2>
                   
                    <a class="btn btn-md btn-theme border" href="{{ route('home') }}"><i class="fas fa-angle-right"></i> Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
