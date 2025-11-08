<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Lizelsundria Urgent Care">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Lizelsundria Urgent Care')</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('img/favicon.png') }}" type="image/x-icon">

    <!-- Bootstrap CSS -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet">

    <!-- Theme CSS -->
    <link href="{{ asset('css/themify-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('css/flaticon-set.css') }}" rel="stylesheet">
    <link href="{{ asset('css/magnific-popup.css') }}" rel="stylesheet">
    <link href="{{ asset('css/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/owl.theme.default.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/animate.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootsnav.css') }}" rel="stylesheet">
    <link href="{{ asset('style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/responsive.css') }}" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;300;400;600;700;800&display=swap"
        rel="stylesheet">
</head>

<body>

    <!-- Toast Container -->
    <div class="toast-container">
        @if (session('success'))
            <div class="toast show align-items-center text-white bg-success border-0" role="alert"
                aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="toast show align-items-center text-white bg-danger border-0" role="alert"
                aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        @endif
    </div>

    <style>
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1100;
        }

        .toast {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            max-width: 350px;
            width: 100%;
            margin-bottom: 15px;
        }

        .toast.show {
            opacity: 1;
        }

        .toast-body {
            padding: 1rem;
            font-weight: 500;
        }

        .btn-close {
            opacity: 0.8;
        }

        .btn-close:hover {
            opacity: 1;
        }
    </style>


    <!-- Header
   ============================================= -->
    @php
        $headerLogoPath = optional($siteSetting)->header_logo_url ?? asset('logo-health-care.png');
        $footerLogoPath = optional($siteSetting)->footer_logo_url ?? asset('logo-health-care.png');
    @endphp

    <header id="home">

        <!-- Start Navigation -->
        <nav class="navbar navbar-default bg-theme dark attr-border bootsnav">

            <!-- Start Top Search -->
            <div class="container">
                <div class="row">
                    <div class="top-search">
                        {{-- <div class="input-group">
                            <form action="#">
                                <input type="text" name="text" class="form-control" placeholder="Search">
                                <button type="submit">
                                    <i class="ti-search" style="color:#086EDD"></i>
                                </button>
                            </form>
                        </div> --}}
                    </div>
                </div>
            </div>
            <!-- End Top Search -->

            <div class="container">

                <!-- Start Atribute Navigation -->
                <div class="attr-nav">
                    <ul>
                        <li class="search">
                            <a href="javascript:void(0);" onclick="window.location='{{ route('doctors') }}'">
                                <i class="fas fa-search" style="color:#086EDD"></i>
                            </a>
                        </li>
                        
                    </ul>
                </div>

                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-menu">
                        <i class="fa fa-bars"></i>
                    </button>
                    <a class="navbar-brand" href="{{ route('home') }}">
                        <img src="{{ $headerLogoPath }}" width="65" height="65"
                            class="logo logo-regular" alt="Logo">
                        <img src="{{ $headerLogoPath }}" width="65" height="65"
                            class="logo desktop" alt="Logo">
                    </a>
                </div>
                <!-- End Header Navigation -->

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="navbar-menu">
                    <ul class="nav navbar-nav navbar-right" data-in="fadeInDown" data-out="fadeOutUp">
                        <li>
                            <a href="{{ route('home') }}">Home</a>
                        </li>
                        <li>
                            <a href="{{ route('department') }}">Departments</a>
                        </li>
                        <li>
                            <a href="{{ route('doctors') }}">Doctors</a>
                        </li>
                        <li>
                            <a href="{{ route('contact') }}">Contact</a>
                        </li>
                        <li>
                            <a href="https://lizelsundria.com/webmail" target="_blank" rel="noopener">Staff Email</a>
                        </li>
                    </ul>
                </div>
            </div>

        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="bg-dark text-light">
        <div class="container">
            <div class="f-items default-padding">
                <div class="row">
                    <div class="col-lg-4 col-md-6 item">
                        <div class="f-item about">
                            <img src="{{ $footerLogoPath }}" width="150" height="150"
                                alt="Logo">
                            <p>
                                Our platform connects patients with experienced and qualified doctors across multiple
                                specialties. From cardiology and neurology to orthopedics and ENT, you can easily find
                                the right doctor.
                            </p>
                            
                        </div>
                    </div>

                    <div class="single-item col-lg-2 col-md-6 item">
                        <div class="f-item link">
                            <h4 class="widget-title">Department</h4>
                            <ul>
                                @foreach ($categories as $category)
                                    <li>
                                        <a href="{{ route('department') }}">{{ $category->name }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="single-item col-lg-2 col-md-6 item">
                        <div class="f-item link">
                            <h4 class="widget-title">Usefull Links</h4>
                            <ul>
                                <li>
                                    <a href="{{ route('home') }}">Home</a>
                                </li>
                                <li>
                                    <a href="{{ route('department') }}">Departments</a>
                                </li>
                                <li>
                                    <a href="{{ route('doctors') }}">Doctors</a>
                                </li>
                                <li>
                                    <a href="{{ route('contact') }}">Contact</a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="single-item col-lg-4 col-md-6 item">
                        <div class="f-item branches">
                            <div class="branches">
                                <div class="address">
                                    <ul>
                                        <li>
                                            <div class="icon">
                                                <i class="flaticon-email"></i>
                                            </div>
                                            <div class="info">
                                                <h5>Email:</h5>
                                                <span>{{ optional($siteSetting)->footer_email ?? 'support@validtemplates.com' }}</span>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="icon">
                                                <i class="flaticon-call"></i>
                                            </div>
                                            <div class="info">
                                                <h5>Phone:</h5>
                                                <span>{{ optional($siteSetting)->footer_phone ?? '+1 (240) 476-9840' }}</span>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- Start Footer Bottom -->
        <div class="footer-bottom">
            <div class="container">
            <div class="row align-center">
                <div class="col-lg-12 text-center">
                <p>Copyright &copy; 2025. Powered by <a href="https://www.kugbahost.com" target="_blank" rel="noopener">KugbaHost</a></p>
                </div>
            </div>
            </div>
        </div>
        <!-- End Footer Bottom -->
    </footer>
    <!-- End Footer -->


    <script>
        // Hide preloader when page is fully loaded
        window.addEventListener('load', function() {
            const preloader = document.querySelector('.se-pre-con');
            if (preloader) {
                preloader.style.display = 'none';
            }
        });
    </script>
    <script src="{{ asset('js/jquery-1.12.4.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/jquery.appear.js') }}"></script>
    <script src="{{ asset('js/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('js/modernizr.custom.13711.js') }}"></script>
    <script src="{{ asset('js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('js/wow.min.js') }}"></script>
    <script src="{{ asset('js/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('js/imagesloaded.pkgd.min.js') }}"></script>
    <script src="{{ asset('js/count-to.js') }}"></script>
    <script src="{{ asset('js/jquery.nice-select.min.js') }}"></script>
    <script src="{{ asset('js/bootsnav.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script>
       

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all toasts
            var toastElList = [].slice.call(document.querySelectorAll('.toast'));
            var toastList = toastElList.map(function(toastEl) {
                return new bootstrap.Toast(toastEl, {
                    autohide: true,
                    delay: 5000
                });
            });

            // Show all toasts
            toastList.forEach(function(toast) {
                toast.show();
            });

            // Auto-hide toasts when clicking the close button
            var closeButtons = document.querySelectorAll('[data-bs-dismiss="toast"]');
            closeButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var toast = bootstrap.Toast.getInstance(button.closest('.toast'));
                    if (toast) {
                        toast.hide();
                    }
                });
            });
        });
    </script>


</body>

</html>
