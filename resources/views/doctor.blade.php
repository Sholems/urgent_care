@extends('layouts.app')

@section('title', 'Lizelsundria Urgent Care - ' . $doctor->name)

@section('content')
<style>
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        z-index: 9999;
        overflow: auto;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .modal.show {
        display: flex !important;
        align-items: center;
        justify-content: center;
        opacity: 1;
    }
    .modal-content {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        width: 90%;
        max-width: 700px;
        transform: translateY(20px);
        transition: transform 0.3s ease, opacity 0.3s ease;
        opacity: 0;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .modal.show .modal-content {
        transform: translateY(0);
        opacity: 1;
    }
    .modal-header {
        border-radius: 16px 16px 0 0;
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        padding: 1.2rem 1.5rem;
        border: none;
    }
    .modal-title {
        font-size: 1.4rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .modal-body {
        padding: 1.5rem;
        background-color: #fff;
        border-radius: 0 0 16px 16px;
    }
    .review-item {
        padding: 1.25rem;
        margin-bottom: 1rem;
        background: #f8f9fa;
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .review-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    .reviewer-name {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.25rem;
    }
    .review-date {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .review-comment {
        margin-top: 0.75rem;
        color: #495057;
        line-height: 1.6;
    }
    .btn-close {
        opacity: 0.8;
        transition: opacity 0.2s ease;
        background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23fff'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat;
    }
    .btn-close:hover {
        opacity: 1;
    }
    .no-reviews {
        text-align: center;
        padding: 2.5rem 1rem;
        color: #6c757d;
    }
    .no-reviews i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.7;
    }
</style>

<script>
function closeModal() {
    const modal = document.getElementById('reviewsModal');
    modal.classList.remove('show');
    document.body.style.overflow = 'auto';
}

window.onclick = function(event) {
    const modal = document.getElementById('reviewsModal');
    if (event.target === modal) {
        closeModal();
    }
}

document.addEventListener('keydown', function(event) {
    const modal = document.getElementById('reviewsModal');
    if (event.key === 'Escape' && modal.classList.contains('show')) {
        closeModal();
    }
});
</script>
<div class="modal" id="reviewsModal" tabindex="-1">
    <div class="modal-content">
        <div class="modal-header">
            <div class="text-center w-100">
                <div class="display-4 fw-bold text-warning mb-1">
                    {{ number_format($doctor->reviews->avg('rating'), 1) }}
                    <span class="text-white-50" style="font-size: 1.5rem">/ 5.0</span>
                </div>
                <div class="d-flex justify-content-center mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star{{ $i <= floor($doctor->reviews->avg('rating')) ? ' text-warning' : ($i - 0.5 <= $doctor->reviews->avg('rating') ? ' text-warning' : ' text-white-50') }}" style="font-size: 1.2rem"></i>
                    @endfor
                </div>
                <p class="text-white-50 mb-0">
                    Based on {{ $doctor->reviews->count() }} {{ Str::plural('review', $doctor->reviews->count()) }}
                </p>
            </div>
            <button type="button" class="btn-close btn-close-white position-absolute" style="top: 1rem; right: 1rem;" onclick="closeModal()" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            @if($doctor->reviews->count() > 0)
                <div class="reviews-container" style="max-height: 60vh; overflow-y: auto; padding-right: 8px;">
                    @foreach($doctor->reviews as $review)
                        <div class="review-item">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    @if($review->user && $review->user->user_photo)
                                        <img src="{{ asset('storage/' . $review->user->user_photo) }}" 
                                             alt="{{ $review->user->user_name }}" 
                                             class="rounded-circle me-3" 
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    @endif
                                    <div>
                                        <div class="reviewer-name">{{ $review->user->user_name ?? 'Anonymous' }}</div>
                                        <div class="d-flex align-items-center">
                                            <div class="text-warning me-2">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}"></i>
                                                @endfor
                                            </div>
                                            <span class="review-date">{{ $review->created_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if(!empty($review->comment))
                                <div class="review-comment">
                                    <p class="mb-0">{{ $review->comment }}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="no-reviews">
                    <i class="far fa-comment-alt"></i>
                    <h5 class="mb-2">No Reviews Yet</h5>
                    <p class="mb-0">Be the first to share your experience!</p>
                </div>
            @endif
        </div>
    </div>
</div>
    <!-- Hero Section with Gradient Background -->
    <div class="breadcrumb-area bg-gradient text-center">
        <!-- Fixed BG -->
        <div class="fixed-bg" style="background-image: url({{ asset('img/shape/9.png') }});"></div>
        <!-- Fixed BG -->
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <h1>Doctor Details</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row g-4">
            <!-- Doctor Image & Quick Info -->
            <div class="col-lg-4">
                <div class="card shadow-lg border-0 sticky-top" style="top: 100px;">
                    <div class="card-body text-center p-4">
                        <div class="position-relative d-inline-block mb-3">
                            <img src="{{ $doctor->photo }}" alt="Dr. {{ $doctor->name }}"
                                class="rounded-circle img-fluid shadow-sm"
                                style="width: 180px; height: 180px; object-fit: cover;">
                        </div>

                        <h3 class="fw-bold text-dark mb-1">{{ $doctor->name }}</h3>
                        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill mb-2">{{ $doctor->categories[0] }}</span>

                        <div class="d-flex justify-content-center align-items-center mb-3">
                            <div class="rating">
                                @for ($i = 1; $i <= $doctor->reviews->avg('rating'); $i++)
                                    <i class="fas fa-star text-warning"></i>
                                @endfor
                                <span class="ms-2 text-muted fw-medium" style="cursor: pointer; color: #0d6efd !important; text-decoration: underline;" onclick="document.getElementById('reviewsModal').classList.add('show'); document.body.style.overflow = 'hidden';">
    {{ number_format($doctor->reviews->avg('rating'), 1) }} ({{ $doctor->reviews->count() }} reviews)
</span>
                            </div>
                        </div>

                        <div class="row text-center g-3 mb-4">
                            <div class="col-4">
                                <div class="p-2 bg-light rounded">
                                    <i class="fas fa-user-md text-primary mb-1"></i>
                                    <small class="d-block text-muted">Specialty</small>
                                    <strong class="text-dark small">{{ $doctor->specialities[0] }}</strong>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded">
                                    <i class="fas fa-clock text-success mb-1"></i>
                                    <small class="d-block text-muted">Experience</small>
                                    <strong class="text-dark small">{{ $doctor->experience }}</strong>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded">
                                    <i class="fas fa-venus-mars text-info mb-1"></i>
                                    <small class="d-block text-muted">Gender</small>
                                    <strong class="text-dark small">{{ ucfirst($doctor->gender) }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-success border-0 mb-3" role="alert">
                            <i class="far fa-clock me-2"></i>
                            <strong>Available Today:</strong> {{ \Carbon\Carbon::parse($doctor->start_time)->format('g:i A') }} - 
                            {{ \Carbon\Carbon::parse($doctor->end_time)->format('g:i A') }}
                        </div>

                        <a href="#appointment" class="btn btn-primary btn-lg w-100 rounded-pill shadow-sm fw-medium">
                            <i class="fas fa-calendar-plus me-2"></i>Book Appointment
                        </a>
                    </div>
                </div>
            </div>

            <!-- Doctor Details -->
            <div class="col-lg-8">
                <div class="row g-4">
                    <!-- Biography Card -->
                    <div class="col-12">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white border-0 pb-0">
                                <h4 class="card-title mb-0 d-flex align-items-center">
                                    <i class="fas fa-user-circle text-primary" style="margin-right: 8px;"></i>About Dr.
                                    {{ $doctor->name }}
                                </h4>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-0 lh-lg">
                                    {{ $doctor->biography }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Medical Information Grid -->
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white border-0 pb-0">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                                    <i class="fas fa-stethoscope text-success me-2" style="margin-right: 8px;"></i>Medical
                                    History
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-0">{{ $doctor->medical_history }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white border-0 pb-0">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                                    <i class="fas fa-shield-alt text-info me-2" style="margin-right: 8px;"></i>Insurance
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-0">{{ $doctor->insurance }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Information -->
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white border-0 pb-0">
                                <h4 class="card-title mb-0 d-flex align-items-center">
                                    <i class="fas fa-info-circle text-warning me-2" style="margin-right: 8px;"></i>Professional Details
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center p-3 bg-light rounded">
                                            <div>
                                                <small class="text-muted d-block">Experience</small>
                                                <strong>{{ $doctor->experience }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center p-3 bg-light rounded">
                                            <div>
                                                <small class="text-muted d-block">Specialties</small>
                                                <strong>{{ $doctor->specialities[0] }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center p-3 bg-light rounded">
                                            <div>
                                                <small class="text-muted d-block">Languages</small>
                                                <strong>{{ $doctor->language }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center p-3 bg-light rounded">
                                            <div>
                                                <small class="text-muted d-block">Location</small>
                                                <strong>{{ $doctor->address }}, {{ $doctor->city }}, {{ $doctor->country }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Appointment Booking Section -->
    <div class="consultation-area default-padding" id="appointment">
        <div class="container">
            <div class="row align-center">
                <div class="col-lg-7 process">
                    <h2>
                        How to Book an <br> Appointment with {{ $doctor->name }}?
                    </h2>
                    <p>
                        Looking to consult with {{ $doctor->name }}, a trusted specialist in {{ $doctor->categories[0] ?? 'Healthcare' }}?
                        Easily schedule your appointment online by selecting a convenient date, time, and providing your details.
                    </p>
                    <div class="row">
                        <div class="col-lg-4 col-md-4 single-item">
                            <div class="item">
                                <i class="flaticon-doctor"></i>
                                <h5>Doctor's Availability</h5>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4 single-item">
                            <div class="item">
                                <i class="flaticon-calendar-1"></i>
                                <h5>Choose A Date</h5>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4 single-item">
                            <div class="item">
                                <i class="flaticon-heartbeat-1"></i>
                                <h5>Confirm Appointment</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 form">
                    <div class="appoinment-box text-center wow fadeInRight">
                        <div class="heading">
                            <h4>Make an Appointment</h4>
                        </div>
                        <form action="{{ route('appointments.book') }}" id="appointmentForm" method="POST">
                            @csrf
                            <div class="row">
                                <!-- Name Field -->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <input class="form-control" id="f_name" name="name" placeholder="Full Name" 
                                               type="text" required>
                                    </div>
                                </div>

                                <!-- Phone Field with Country Code Picker -->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <input class="form-control" name="email" placeholder="Email" 
                                               type="email" required>
                                    </div>
                                </div>

                                <!-- Gender Selection -->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <select name="gender" class="form-control" required>
                                            <option value="">Select Gender</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="child">Child</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Hidden Department Field -->
                                <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">

                                <!-- Date Picker -->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <input class="form-control" id="f_date" name="date" placeholder="Select Date" 
                                               type="text" readonly required>
                                    </div>
                                </div>

                                <!-- Time Slot Selection -->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label text-start d-block mb-3 text-white">Select Time Slot:</label>
                                        <div id="time-slots-container" class="time-slots d-flex flex-wrap gap-2 justify-content-center">
                                            <!-- Time slots will be populated dynamically -->
                                            <div class="text-white">
                                                <i class="fas fa-calendar-alt me-2"></i>
                                                Please select a date first to view available time slots
                                            </div>
                                        </div>
                                        <input type="hidden" id="selected_time" name="time" required>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="col-md-12">
                                    <button type="submit" name="submit" id="f_submit" class="btn btn-primary">
                                        Book Appointment
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Required CDN Links and Scripts -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.2.1/css/intlTelInput.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/themes/material_blue.min.css" rel="stylesheet">

    <style>
        .hero-section {
            min-height: 300px;
        }

        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
        }

        .btn {
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .rating .fas {
            font-size: 14px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #6c63ff;
            box-shadow: 0 0 0 0.2rem rgba(108, 99, 255, 0.25);
        }

        .sticky-top {
            z-index: 1020;
        }

        /* Time Slots */
        .time-slots-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            width: 100%;
            margin-top: 10px;
        }

        .time-slot-item {
            position: relative;
            width: 100%;
        }

        .time-slot-radio {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .time-slot-label {
            display: block;
            padding: 6px 4px;
            text-align: center;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 13px;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .time-slot-label:hover:not(.booked) {
            border-color: #86b7fe;
            background-color: rgba(13, 110, 253, 0.05);
        }

        .time-slot-radio:checked + .time-slot-label {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        .time-slot-label.booked {
            background-color: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
            opacity: 0.7;
            text-decoration: line-through;
        }

        /* Phone Input Styles */
        .iti {
            width: 100%;
        }

        .iti__flag-container {
            border-right: 1px solid #ddd;
        }

        /* Enhanced Date Picker Styles */
        .flatpickr-input {
            cursor: pointer;
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 10px;
            font-size: 16px;
            width: 100%;
        }

        .flatpickr-calendar {
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .flatpickr-day {
            border-radius: 4px;
            font-size: 14px;
            transition: background-color 0.2s ease;
        }

        .flatpickr-day:hover {
            background-color: #e6f0fa;
        }

        .flatpickr-day.selected,
        .flatpickr-day.startRange,
        .flatpickr-day.endRange {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #fff !important;
        }

        .flatpickr-day.today {
            border-color: #6c63ff;
            background-color: rgba(108, 99, 255, 0.1);
        }

        .flatpickr-monthDropdown-months,
        .flatpickr-year {
            font-size: 16px;
            font-weight: 500;
            color: #333;
            padding: 5px;
        }

        .flatpickr-prev-month,
        .flatpickr-next-month {
            font-size: 18px;
            color: #0d6efd;
            padding: 5px;
        }

        .flatpickr-prev-month:hover,
        .flatpickr-next-month:hover {
            color: #0056b3;
            background-color: #e6f0fa;
            border-radius: 50%;
        }

        .flatpickr-month {
            margin: 10px 0;
            padding: 5px;
        }

        .flatpickr-current-month {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        /* Form Group Spacing */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.5rem;
        }

        /* Loading state */
        .time-slots-loading {
            text-align: center;
            padding: 20px;
        }

        @media (max-width: 768px) {
            .sticky-top {
                position: relative !important;
            }

            .hero-section {
                min-height: 200px;
            }

            .display-5 {
                font-size: 2rem;
            }

            .flatpickr-calendar {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.2.1/js/intlTelInput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const doctorId = {{ $doctor->id }};
            const doctorStartTime = '{{ $doctor->start_time }}';
            const doctorEndTime = '{{ $doctor->end_time }}';
            
            

            // Initialize Flatpickr for Date Selection with Enhanced Configuration
            flatpickr("#f_date", {
                dateFormat: "Y-m-d",
                minDate: "today",
                maxDate: new Date().fp_incr(365), // Allow selection up to 1 year
                disable: [
                    function(date) {
                        // Disable Sundays (0 = Sunday)
                        return (date.getDay() === 0);
                    }
                ],
                enableYearDropdown: true, // Enable year dropdown
                yearDropdown: true, // Show year dropdown
                enableMonthDropdown: true, // Enable month dropdown
                showMonths: 1, // Display one month at a time
                inline: false, // Keep it as a dropdown
                prevArrow: '<i class="fas fa-chevron-left"></i>',
                nextArrow: '<i class="fas fa-chevron-right"></i>',
                onChange: function(selectedDates, dateStr, instance) {
                    if (dateStr) {
                        loadTimeSlots(dateStr, doctorId);
                    }
                }
            });

            // Function to generate time slots based on doctor's availability
            function generateTimeSlots(startTime, endTime) {
                const slots = [];
                console.log("Generating slots from", startTime, "to", endTime);
                // startTime and endTime are in "HH:MM AM/PM" format
                // Parse the time strings manually to ensure proper handling
                
                const parseTime = (time) => {
                    const [hours, minutes] = time.match(/\d+/g).map(Number);
                    const isPM = time.toLowerCase().includes('pm');
                    return new Date(2000, 0, 1, isPM && hours !== 12 ? hours + 12 : hours % 12, minutes);
                };

                const start = parseTime(startTime);
                const end = parseTime(endTime);
                console.log(start, end);
                
                let current = new Date(start);
                console.log(current)
                while (current < end) {
                    console.log("here in while");
                    const timeString = current.toTimeString().slice(0, 5); // HH:MM format
                    const displayTime = current.toLocaleTimeString([], { 
                        hour: 'numeric', 
                        minute: '2-digit', 
                        hour12: true 
                    });

                    console.log(timeString, displayTime);
                    
                    slots.push({
                        value: timeString,
                        display: displayTime
                    });
                    
                    console.log("before increment", slots);
                    current.setMinutes(current.getMinutes() + 30); // 30-minute intervals
                }
                
                return slots;
            }

            // Function to load available time slots for selected date
            function loadTimeSlots(selectedDate, doctorId) {
                const timeSlotsContainer = document.getElementById('time-slots-container');
                
                // Show loading state
                timeSlotsContainer.innerHTML = `
                    <div class="time-slots-loading">
                        <i class="fas fa-spinner fa-spin me-2"></i>
                        Loading available time slots...
                    </div>
                `;
                console.log(doctorStartTime);
                console.log(doctorEndTime);
                // Generate all possible time slots
                const allTimeSlots = generateTimeSlots(doctorStartTime, doctorEndTime);

                // Fetch booked appointments for this date and doctor
                fetch(`/api/appointments/booked-slots`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        date: selectedDate,
                        doctor_id: doctorId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    const bookedSlots = data.booked_slots || [];
                   
                    renderTimeSlots(allTimeSlots, bookedSlots);
                })
                .catch(error => {
                    console.error('Error fetching booked slots:', error);
                    // Fallback: render all slots as available
                    renderTimeSlots(allTimeSlots, []);
                });
            }

            // Function to render time slots
            function renderTimeSlots(allSlots, bookedSlots) {
                const timeSlotsContainer = document.getElementById('time-slots-container');
                const selectedTimeInput = document.getElementById('selected_time');
        
                
                if (allSlots.length === 0) {
                    timeSlotsContainer.innerHTML = `
                        <div class="text-muted">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            No time slots available for this date
                        </div>
                    `;
                    return;
                }

                let slotsHtml = '';
                
                // Add a container for the time slots grid
                slotsHtml += '<div class="time-slots-grid">';
                
                allSlots.forEach(slot => {
                    const isBooked = bookedSlots.includes(slot.value);
                    const buttonClass = isBooked ? 'time-slot-btn booked' : 'time-slot-btn';
                    const disabled = isBooked ? 'disabled' : '';
                    const title = isBooked ? 'This time slot is already booked' : '';
                    
                    slotsHtml += `
                        <div class="time-slot-item">
                            <input type="radio" 
                                   id="slot-${slot.value}" 
                                   name="time_slot" 
                                   value="${slot.value}" 
                                   ${disabled}
                                   class="time-slot-radio">
                            <label for="slot-${slot.value}" 
                                   class="time-slot-label ${buttonClass}" 
                                   title="${title}">
                                ${slot.display}
                                ${isBooked ? '<i class="fas fa-times"></i>' : ''}
                            </label>
                        </div>`;
                });
                
                slotsHtml += '</div>'; // Close time-slots-grid
                
                timeSlotsContainer.innerHTML = slotsHtml;

                // Add event listeners to time slot radio buttons
                const timeSlotRadios = timeSlotsContainer.querySelectorAll('.time-slot-radio:not(:disabled)');
                
                timeSlotRadios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        if (this.checked) {
                            // Remove active class from all labels
                            document.querySelectorAll('.time-slot-label').forEach(label => {
                                label.classList.remove('active');
                            });
                            
                            // Add active class to selected label
                            const label = this.nextElementSibling;
                            if (label && label.classList.contains('time-slot-label')) {
                                label.classList.add('active');
                            }
                            
                            // Set the selected time value
                            selectedTimeInput.value = this.value;
                        }
                    });
                });

                // Clear previously selected time
                selectedTimeInput.value = '';
            }

            // Form Validation
            document.getElementById('appointmentForm').addEventListener('submit', function(e) {
                const name = document.getElementById('f_name').value.trim();
                const email = document.getElementById('email').value.trim();
                const date = document.getElementById('f_date').value;
                const time = document.getElementById('selected_time').value;
                const gender = document.querySelector('select[name="gender"]').value;

                if (!name) {
                    alert('Please enter your full name');
                    e.preventDefault();
                    return;
                }

                if (!email) {
                    alert('Please enter your email');
                    e.preventDefault();
                    return;
                }
              

                if (!gender) {
                    alert('Please select your gender');
                    e.preventDefault();
                    return;
                }

                if (!date) {
                    alert('Please select an appointment date');
                    e.preventDefault();
                    return;
                }

                if (!time) {
                    alert('Please select a time slot');
                    e.preventDefault();
                    return;
                }

            });
        });
    </script>
@endsection