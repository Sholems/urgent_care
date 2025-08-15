<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// return Product::where('is_active', 1)->get();

class DoctorController extends Controller {
  /**
   * Display a listing of the resource.
   */
  public function index() {
    $doctors = Doctor::where('is_available', 1)
      ->withAvg('reviews as average_rating', 'rating')
      ->get();

    $doctors->map(function ($doctor) {
      $doctor->certificates = json_decode($doctor->certificates, true);
      $doctor->average_rating = $doctor->average_rating ?? 0;
      return $doctor;
    });

    return $doctors;
  }

  public function search(Request $request) {
    $builder = Doctor::query()->where('is_available', 1);

    if ($query = $request->input('search')) {
      $builder->where(function ($q) use ($query) {
        $q->where('name', 'like', "%{$query}%")
          ->orWhere('categories', 'like', "%{$query}%");
      });
    }

    $builder = $this->applyFilters($request, $builder);

    return $builder->get()->map(function ($doctor) {
      $doctor->certificates = json_decode($doctor->certificates, true);
      $doctor->average_rating = $doctor->average_rating ?? 0;
      return $doctor;
    });
  }

  public function categorySearch(Request $request, $category) {
    $builder = Doctor::query()
      ->where('is_available', 1)
      ->where('categories', 'like', "%{$category}%");

    $builder = $this->applyFilters($request, $builder);

    return $builder->get()->map(function ($doctor) {
      $doctor->certificates = json_decode($doctor->certificates, true);
      $doctor->average_rating = $doctor->average_rating ?? 0;
      return $doctor;
    });
  }

  private function applyFilters(Request $request, $query) {
    if ($request->filled('min_price')) {
      $query->where('price', '>=', $request->input('min_price'));
    }

    if ($request->filled('max_price')) {
      $query->where('price', '<=', $request->input('max_price'));
    }

    if ($request->filled('available_start')) {
      $availableStart = Carbon::createFromFormat('H:i', $request->input('available_start'))->format('H:i:s');
      $query->whereTime(DB::raw("STR_TO_DATE(end_time, '%h:%i %p')"), '>=', $availableStart);
    }

    if ($request->filled('available_end')) {
      $availableEnd = Carbon::createFromFormat('H:i', $request->input('available_end'))->format('H:i:s');
      $query->whereTime(DB::raw("STR_TO_DATE(start_time, '%h:%i %p')"), '<=', $availableEnd);
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

  // public function show($userId) {
  //   return Appointment::where('user_id', $userId)->get();
  // }


  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Doctor $doctor) {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Doctor $doctor) {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Doctor $doctor) {
    //
  }
}
