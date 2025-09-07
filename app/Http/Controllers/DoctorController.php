<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Review;
use App\Models\User;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\AppUser;
use App\Models\Appointment;
use Illuminate\Support\Collection;

// return Product::where('is_active', 1)->get();

class DoctorController extends Controller {
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request) {
    $perPage = (int) $request->query('per_page', 15);
    $page    = (int) $request->query('page', 1);
    $perPage = max(1, min($perPage, 100));

    $paginator = Doctor::where('is_available', 1)
      ->withAvg('reviews as average_rating', 'rating')
      ->orderByDesc('id')
      ->paginate($perPage, ['*'], 'page', $page);

    $paginator->getCollection()->transform(function ($doctor) {
      $doctor->certificates = json_decode($doctor->certificates, true);
      $doctor->average_rating = $doctor->average_rating ?? 0;
      return $doctor;
    });

    return $this->paginatedResponse($paginator);
  }

  public function search(Request $request) {
    $perPage = (int) $request->query('per_page', 15);
    $page    = (int) $request->query('page', 1);
    $perPage = max(1, min($perPage, 100));

    $builder = Doctor::query()->where('is_available', 1);

    if ($query = $request->input('search')) {
      $builder->where(function ($q) use ($query) {
        $q->where('name', 'like', "%{$query}%")
          ->orWhere('categories', 'like', "%{$query}%")
          ->orWhere('gender', 'like', "%{$query}%")
          ->orWhere('language', 'like', "%{$query}%");

        $categories = Category::where('symptoms', 'like', "%{$query}%")->pluck('name');
        foreach ($categories as $catName) {
          $q->orWhere('categories', 'like', "%{$catName}%");
        }
      });
    }

    $builder = $this->applyFilters($request, $builder)->orderByDesc('id');

    $paginator = $builder->paginate($perPage, ['*'], 'page', $page);
    $paginator->getCollection()->transform(function ($doctor) {
      $doctor->certificates = json_decode($doctor->certificates, true);
      $doctor->average_rating = $doctor->average_rating ?? 0;
      return $doctor;
    });
    return $this->paginatedResponse($paginator);
  }

  public function categorySearch(Request $request, $category) {
    $perPage = (int) $request->query('per_page', 15);
    $page    = (int) $request->query('page', 1);
    $perPage = max(1, min($perPage, 100));

    $builder = Doctor::query()->where('is_available', 1);

    // Filter by the provided category or related symptoms
    $builder->where(function ($q) use ($category) {
      $q->where('categories', 'like', "%{$category}%");

      $categories = Category::where('symptoms', 'like', "%{$category}%")->pluck('name');
      foreach ($categories as $catName) {
        $q->orWhere('categories', 'like', "%{$catName}%");
      }
    });

    // Optional search term across doctor name, categories and symptoms
    if ($query = $request->input('search')) {
      $builder->where(function ($q) use ($query) {
        $q->where('name', 'like', "%{$query}%")
          ->orWhere('categories', 'like', "%{$query}%");

        $categories = Category::where('symptoms', 'like', "%{$query}%")->pluck('name');
        foreach ($categories as $catName) {
          $q->orWhere('categories', 'like', "%{$catName}%");
        }
      });
    }

    $builder = $this->applyFilters($request, $builder)->orderByDesc('id');

    $paginator = $builder->paginate($perPage, ['*'], 'page', $page);
    $paginator->getCollection()->transform(function ($doctor) {
      $doctor->certificates = json_decode($doctor->certificates, true);
      $doctor->average_rating = $doctor->average_rating ?? 0;
      return $doctor;
    });
    return $this->paginatedResponse($paginator);
  }

  // LOGIN //
  public function login(Request $request) {

    $request->validate([
      'email' => 'required|email',
      'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
      return response()->json([
        'message' => 'Invalid email or password',
      ], 401);
    }

    $doctor = $user->doctor;

    if (!$doctor) {
      return response()->json([
        'message' => 'Doctor not found',
      ], 404);
    }

    $perPage = (int) $request->input('per_page', 15);
    $page    = (int) $request->input('page', 1);
    $perPage = max(1, min($perPage, 100));

    $appointments = $doctor->appointments()
      ->with(['user', 'review'])
      ->orderByDesc('id')
      ->paginate($perPage, ['*'], 'page', $page);

    return response()->json([
      'message' => 'Doctor logged in successfully',
      'doctor' => $doctor,
      'appointments' => $this->formatPaginated($appointments),
    ], 200);
  }

  private function applyFilters(Request $request, $query) {
    if ($request->filled('min_price')) {
      $query->where('price', '>=', $request->input('min_price'));
    }

    if ($request->filled('max_price')) {
      $query->where('price', '<=', $request->input('max_price'));
    }

    if ($request->filled('gender')) {
      $query->where('gender', $request->input('gender'));
    }

    if ($request->filled('language')) {
      $query->where('language', 'like', "%" . $request->input('language') . "%");
    }

    if ($request->filled('available_start')) {
      $availableStart = Carbon::createFromFormat('H:i', $request->input('available_start'))->format('H:i:s');
      $query->whereTime(DB::raw("STR_TO_DATE(end_time, '%h:%i %p')"), '>', $availableStart);
    }

    if ($request->filled('available_end')) {
      $availableEnd = Carbon::createFromFormat('H:i', $request->input('available_end'))->format('H:i:s');
      $query->whereTime(DB::raw("STR_TO_DATE(start_time, '%h:%i %p')"), '<', $availableEnd);
    }

    $query->withAvg('reviews as average_rating', 'rating');

    if ($request->filled('min_rating')) {
      $query->havingRaw('COALESCE(average_rating, 0) >= ?', [$request->input('min_rating')]);
    }

    switch ($request->input('sort')) {
      case 'low_price':
        $query->orderBy('price', 'asc');
        break;
      case 'high_price':
        $query->orderBy('price', 'desc');
        break;
      case 'newest':
        $query->orderBy('created_at', 'desc');
        break;
      case 'most_popular':
        $query
          ->withCount('appointments')
          ->orderBy('appointments_count', 'desc');
        break;
      case 'best_match':
      default:
        break;
    }

    return $query;
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create() {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request) {
    //
  }

  /**
   * Display the specified resource.
   */
  public function show($doctorId) {
    $doctor = Doctor::where('id', $doctorId)
      ->withAvg('reviews as average_rating', 'rating')
      ->first();

    $doctor->certificates = json_decode($doctor->certificates, true);
    $doctor->average_rating = $doctor->average_rating ?? 0;
    $doctor->reviews = Review::where('doctor_id', $doctorId)->with('user')->get();

    return $doctor;
  }

  /**
   * List all patients for the specified doctor.
   */
  public function patients(Request $request, Doctor $doctor)
  {
    $perPage = (int) $request->query('per_page', 15);
    $page    = (int) $request->query('page', 1);
    $perPage = max(1, min($perPage, 100));

    // Load patients with their appointments (filtered by this doctor)
    $builder = $doctor->patients()
      ->with([
        'appointments' => function ($q) use ($doctor) {
          $q
            ->where('doctor_id', $doctor->id)
            ->orderByDesc('id')
            ->with(['documents', 'review']);
        },
      ])
      ->distinct();

    if ($search = $request->input('search')) {
      $builder->where(function ($q) use ($search) {
        $q->where('user_name', 'like', "%{$search}%")
          ->orWhere('user_email', 'like', "%{$search}%")
          ->orWhere('user_phone_number', 'like', "%{$search}%");
      });
    }

    $paginator = $builder->paginate($perPage, ['app_users.*'], 'page', $page);

    // Add last_appointment_date to each item in the page
    $paginator->getCollection()->transform(function ($patient) {
      $last = $patient->appointments->first();
      $patient->last_appointment_date = $last ? $last->date : null;
      return $patient;
    });

    return $this->paginatedResponse($paginator);
  }
  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Doctor $doctor) {
    //
  }

  /**
   * Provide monthly appointment, patient and earning stats for a doctor.
   */
  public function earningStats(Doctor $doctor)
  {
    $monthly = Appointment::where('appointments.doctor_id', $doctor->id)
      ->where('appointments.status', 'accepted')
      ->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
      ->selectRaw(
        "DATE_FORMAT(STR_TO_DATE(appointments.date, '%Y-%m-%d'), '%Y-%m') as month, " .
        'COUNT(*) as appointments, ' .
        'COUNT(DISTINCT appointments.user_id) as patients, ' .
        'SUM(doctors.price) as earnings'
      )
      ->groupBy('month')
      ->orderBy('month')
      ->get();

    $totalAppointments = $monthly->sum('appointments');
    $totalEarnings = $monthly->sum('earnings');

    $totalPatients = Appointment::where('doctor_id', $doctor->id)
      ->where('status', 'accepted')
      ->distinct('user_id')
      ->count('user_id');

    return response()->json([
      'doctor_id' => $doctor->id,
      'totalAppointments' => (int) $totalAppointments,
      'totalPatients' => (int) $totalPatients,
      'totalEarnings' => (float) $totalEarnings,
      'monthlyStats' => $monthly->map(function ($row) {
        return [
          'month' => $row->month,
          'appointments' => (int) $row->appointments,
          'patients' => (int) $row->patients,
          'earnings' => (float) $row->earnings,
        ];
      }),
    ]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Doctor $doctor) {
    $data = $request->validate([
      'name' => 'sometimes|string',
      'biography' => 'sometimes|string',
      'price' => 'sometimes|numeric',
      'experience' => 'sometimes|integer',
      'categories' => 'sometimes|array',
      'specialities' => 'sometimes|array',
      'is_available' => 'sometimes|boolean',
      'start_time' => 'sometimes',
      'end_time' => 'sometimes',
      'gender' => 'sometimes|string',
      'promotion' => 'sometimes|string',
      'city' => 'sometimes|string',
      'address' => 'sometimes|string',
      'country' => 'sometimes|string',
      'latitude' => 'sometimes|numeric',
      'longitude' => 'sometimes|numeric',
    ]);

    if ($request->has('categories')) {
      $data['categories'] = $request->input('categories');
    }

    if ($request->has('specialities')) {
      $data['specialities'] = $request->input('specialities');
    }

    if ($request->has(['latitude', 'longitude'])) {
      $data['latitude'] = $request->input('latitude');
      $data['longitude'] = $request->input('longitude');
    }

    $doctor->fill($data);
    $doctor->save();

    return response()->json([
      'message' => 'Doctor updated successfully',
      'doctor' => $doctor,
    ]);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Doctor $doctor) {
    //
  }
}
