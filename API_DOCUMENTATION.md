# API Documentation

This document describes the available API endpoints for the Kugba Urgent Care backend. All endpoints are prefixed with `/api` and require authentication via the `authApi` middleware unless otherwise specified.

## Authentication and Users

### `POST /api/user/create`
Create a new application user.

**Parameters**
- `user_name` (string, required)
- `user_email` (string, required, email)
- `user_password` (string, required)

**Sample Response**
```json
{
  "message": "User created successfully",
  "user": {
    "id": 1,
    "user_name": "John Doe",
    "user_email": "john@example.com"
  }
}
```

### `POST /api/user/login`
Authenticate an application user.

**Parameters**
- `user_email` (string, required, email)
- `user_password` (string, required)

**Sample Response**
```json
{
  "message": "User logged in successfully",
  "user": {
    "id": 1,
    "user_name": "John Doe",
    "user_email": "john@example.com"
  }
}
```

### `POST /api/user/change-password`
Change an application user's password.

**Parameters**
- `user_email` (string, required, email)
- `new_password` (string, required)

**Sample Response**
```json
{
  "message": "Password changed successfully",
  "user": { "id": 1 }
}
```

### `POST /api/user/update/{id}`
Update profile details for a user.

**Parameters** (all optional)
- `user_name`, `user_email`, `user_location`, `user_phone_number`, `medical_history`, `insurance_details`

**Sample Response**
```json
{
  "message": "User updated successfully",
  "user": { "id": 1, "user_name": "Jane" }
}
```

### `POST /api/doctor/login`
Authenticate a doctor and retrieve recent appointments.

**Parameters**
- `email` (string, required)
- `password` (string, required)

**Sample Response**
```json
{
  "message": "Doctor logged in successfully",
  "doctor": { "id": 5, "name": "Dr. Smith" },
  "appointments": {
    "data": [ /* appointment list */ ],
    "pagination": { /* pagination info */ }
  }
}
```

### `POST /api/doctor/edit/{doctor}`
Update doctor profile details. Accepts fields such as `name`, `biography`, `price`, `experience`, `categories`, `specialities`, `is_available`, `start_time`, `end_time`, `gender`, `promotion`, `city`, `address`, `country`, `latitude`, and `longitude`.

### `POST /api/users/change-password`
Change password for an admin user.

**Parameters**
- `email` (string, required)
- `otp` (string, required)
- `new_password` (string, required)

---

## Doctors

### `GET /api/doctors`
List available doctors with optional filters.

**Query Parameters**
- `per_page` (int)
- `page` (int)
- `search` (string)
- `min_price`, `max_price` (numeric)
- `gender` (string)
- `latitude`, `longitude` (numeric)

**Sample Response**
```json
{
  "data": [
    { "id": 5, "name": "Dr. Smith", "average_rating": 4.5 }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

### `GET /api/doctors/search`
Search doctors using the same filters as above plus a `search` term.

### `GET /api/doctors/category/{category}`
List doctors by category with optional `search` and `top` query parameters. When `top` is provided, the response returns the highest rated doctors up to the specified limit.

### `GET /api/doctors/{doctorId}`
Retrieve full details for a specific doctor.

### `GET /api/doctors/{doctor}/patients`
List patients for a doctor.

**Query Parameters**
- `per_page`, `page`
- `search` (string)

**Sample Response**
```json
{
  "data": [
    { "id": 3, "user_name": "Alice", "last_appointment_date": "2024-01-10" }
  ],
  "pagination": { /* pagination info */ }
}
```

### `GET /api/doctors/{doctor}/earning-stats`
Monthly statistics for a doctor including appointment count, patient count and earnings.

**Sample Response**
```json
{
  "doctor_id": 5,
  "totalAppointments": 12,
  "totalPatients": 8,
  "totalEarnings": 2400,
  "monthlyStats": [
    { "month": "2024-01", "appointments": 5, "patients": 4, "earnings": 1000 }
  ]
}
```

---

## Static Resources

- `GET /api/banners`
- `GET /api/categories`
- `GET /api/promotions`
- `GET /api/diagnostics`
- `GET /api/specialities`
- `GET /api/stripe-keys` – returns publishable and secret Stripe keys.

Each endpoint returns an array of items or key details.

---

## Appointments

### `GET /api/appointments`
List all appointments. Supports `per_page` and `page` query parameters.

### `GET /api/appointments/{userId}`
List appointments for a user.

### `GET /api/appointments/doctor/{doctorId}`
List appointments for a doctor including user and review details.

### `GET /api/appointments/doctor/{doctorId}/available-slots`
Retrieve available half-hour slots for a doctor on a specific date.

**Query Parameters**
- `date` (string, required, `YYYY-MM-DD`)

**Sample Response**
```json
{
  "status": "success",
  "message": "Available slots fetched successfully",
  "data": ["09:00", "09:30"]
}
```

### `POST /api/appointments/create`
Create a new appointment.

**Parameters**
- `date` (string, required, date)
- `time` (string, required)
- `user_id` (integer, required)
- `doctor_id` (integer, required)
- `status` (string, required)
- `payment_intent_id` (string, optional)

**Sample Response**
```json
{
  "status": "success",
  "message": "Appointment created successfully",
  "data": { "id": 10 }
}
```

### `POST /api/appointments/{id}/reschedule`
Reschedule an existing appointment.

**Parameters**
- `date` (string, `Y-m-d`)
- `time` (string, `H:i`)

### `POST /api/appointments/{id}/documents`
Upload a document for an appointment.

**Parameters**
- `file` (file, required)
- `type` (string, required: `prescription`, `medical_report`, `other`, `invoice`)

### `DELETE /api/appointments/{id}`
Cancel an appointment and issue a refund if applicable.

---

## Notifications

- `GET /api/notifications/{userId}` – list notifications for a user.
- `GET /api/notifications/doctor/{doctorId}` – list notifications for a doctor.
- `POST /api/notifications/{id}/read` – mark a notification as read.

Each listing endpoint supports `per_page` and `page` query parameters and returns paginated results.

---

## Favourites

- `GET /api/favourites/{userId}` – list favourite records for a user.
- `GET /api/favourites/doctors/{userId}` – list favourited doctors for a user.
- `POST /api/favourites/create` – body parameters: `user_id`, `doctor_id`.
- `DELETE /api/favourites/{userId}/{doctorId}` – remove a favourite.

---

## Reviews

- `POST /api/reviews/create` – parameters: `appointment_id`, `rating`, `comment`.
- `GET /api/reviews/doctor/{doctorId}` – list reviews for a doctor.

---

## OTP and Verification

- `POST /api/otp/send` – send email OTP (`email`).
- `POST /api/otp/verify` – verify email OTP (`email`, `otp`).
- `POST /api/phone-otp/send` – send SMS OTP (`phone`).
- `POST /api/phone-otp/verify` – verify SMS OTP (`phone`, `code`, `user_email`).

---

## Dashboard

- `GET /api/dashboard/doctor/{doctorId}` – earnings for a doctor.
- `GET /api/dashboard/admin` – revenue for all doctors.

---

## Exchange Rate

- `GET /api/exchange-rate` – latest USD to SLL rate.

---

## Payments

- `POST /api/payments/payment-intent` – create a payment intent (`amount`, `userId`).
- `POST /api/payments/setup-intent/{userId}` – create a setup intent for saved cards.
- `GET /api/payments/methods/{userId}` – list saved payment methods.
- `DELETE /api/payments/methods/{paymentMethodId}` – remove a payment method.

---

## Users

- `GET /api/user/{userId}` – retrieve full user details including appointments.
- `GET /api/patients/{userId}/appointments` – list patient appointments with doctors.

Each listing endpoint returns paginated data in the standard format:
```json
{
  "data": [ /* items */ ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 15
  }
}
```

