<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: Redesigned Quick Info Cards | Lizelsundria Urgent Care</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            padding: 40px 0 80px;
        }

        .preview-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .preview-header h1 {
            font-weight: 800;
            font-size: 32px;
            color: #0f172a;
            letter-spacing: -0.5px;
        }

        .preview-header p {
            color: #64748b;
            font-size: 16px;
            max-width: 600px;
            margin: 10px auto 0;
        }

        /* Modern Quick Info Section */
        .modern-quick-info {
            position: relative;
        }

        .quick-info-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 36px 30px 32px;
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
            font-size: 26px;
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
</head>
<body>

<div class="container">
    <div class="preview-header">
        <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2 rounded-pill mb-2">Redesign Option 1 Preview</span>
        <h1>Modern Healthcare Quick Info</h1>
        <p>Interactive cards with clickable phone dialers, live schedule styling, and clear call-to-actions.</p>
        <div class="mt-3">
            <a href="/" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-1"></i> View Full Homepage</a>
        </div>
    </div>

    <!-- Feature Entry Area -->
    <div class="modern-quick-info">
        <div class="row align-items-stretch">
            
            <!-- Card 1: Emergency -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="quick-info-card emergency-card">
                    <div class="card-header-badge">
                        <span class="badge-urgent"><i class="fas fa-ambulance"></i> Urgent Care</span>
                    </div>
                    <div class="card-icon-wrapper red-accent">
                        <i class="fas fa-truck-medical"></i>
                    </div>
                    <h4 class="card-title">Emergency Case</h4>
                    <p class="card-desc">
                        For urgent cases and emergency home visits, contact our response team immediately for prompt medical attention.
                    </p>
                    <div class="mt-auto">
                        <a href="tel:+23272735530" class="btn btn-call-emergency w-100 mb-2">
                            <i class="fas fa-phone-alt me-2"></i> Call: +232 72 735 530
                        </a>
                        <div class="text-center mt-2">
                            <span class="d-block small text-muted mb-1">Additional Lines:</span>
                            <div class="d-flex flex-wrap gap-1 justify-content-center">
                                <a href="tel:+23278100017" class="alt-phone-pill me-1 mb-1">+232 78 100 017</a>
                                <a href="tel:+23230439975" class="alt-phone-pill me-1 mb-1">+232 30 439 975</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Opening Hours -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="quick-info-card hours-card">
                    <div class="card-header-badge">
                        <span class="badge-hours"><i class="fas fa-clock"></i> Clinical Hours</span>
                    </div>
                    <div class="card-icon-wrapper blue-accent">
                        <i class="fas fa-clock-rotate-left"></i>
                    </div>
                    <h4 class="card-title text-white">Opening Hours</h4>
                    <div class="hours-schedule">
                        <div class="schedule-row">
                            <span class="schedule-day">Mon &ndash; Sat:</span>
                            <span class="schedule-time">8:00 am to 04:00 pm</span>
                        </div>
                        <div class="schedule-row">
                            <span class="schedule-day">Sun &amp; Holidays:</span>
                            <span class="schedule-time">Appointment/Emergency</span>
                        </div>
                    </div>
                    <div class="hours-note">
                        <i class="fas fa-shield-halved me-1"></i> Emergency home visits &amp; telephone consultations available on-call.
                    </div>
                </div>
            </div>

            <!-- Card 3: General Consultation -->
            <div class="col-lg-4 col-md-12 mb-4">
                <div class="quick-info-card consultation-card">
                    <div class="card-header-badge">
                        <span class="badge-consultation"><i class="fas fa-user-md"></i> Home &amp; Clinic</span>
                    </div>
                    <div class="card-icon-wrapper teal-accent">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <h4 class="card-title">General Consultation</h4>
                    <p class="card-desc">
                        Book a general consultation for routine check-ups, health advice, or home care treatment in Sierra Leone.
                    </p>
                    <div class="mt-auto">
                        <a href="/contact?enquiry=homecare#contact" class="btn btn-book-consult w-100 mb-2">
                            <i class="fas fa-calendar-check me-2"></i> Book Consultation
                        </a>
                        <div class="consultation-phone text-center small text-muted mt-2">
                            Direct Phone: <a href="tel:+23272735530">+232 72 735 530</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
