<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\AppUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Contact;
use App\Models\AdminBankDetail;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::all()->take(3);
        $doctors = Doctor::all()->take(3);
        return view('home', compact('categories', 'doctors'));
    }

    public function department()
    {
        $categories = Category::all();
        return view('departments', compact('categories'));
    }

    public function doctors(Request $request)
    {
        $doctors = Doctor::with('reviews');
    
        // --- Search ---
        if ($request->filled('search')) {
            $search = $request->search;
            $doctors->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                      ->orWhere('categories', 'like', "%$search%")
                      ->orWhereRaw('JSON_SEARCH(categories, "one", ?) IS NOT NULL', ["%$search%"]);
            });
        }
    
        // --- Rating filter ---
        if ($request->filled('rating')) {
            $doctors->where('rating', '>=', (float) $request->rating);
        }
    
        // --- Gender filter ---
        if ($request->filled('gender')) {
            $doctors->where('gender', $request->gender);
        }
    
        // --- Price filters ---
        if ($request->filled('minPrice')) {
            $doctors->where('price', '>=', (float) $request->minPrice);
        }
        if ($request->filled('maxPrice')) {
            $doctors->where('price', '<=', (float) $request->maxPrice);
        }
    
        // --- Time slots filter ---
        if ($request->filled('timeSlots')) {
            $timeSlots = $request->timeSlots;
        
            $doctors->where(function ($query) use ($timeSlots) {
                foreach ($timeSlots as $slot) {
                    [$slotStart, $slotEnd] = explode('-', $slot);
                    $slotStartTime = Carbon::parse($slotStart)->format('H:i:s');
                    $slotEndTime = Carbon::parse($slotEnd)->format('H:i:s');
        
                    $query->orWhere(function ($q) use ($slotStartTime, $slotEndTime) {
                        // Doctor is available in this slot
                        $q->where('start_time', '<=', $slotStartTime)
                          ->where('end_time', '>=', $slotEndTime)
                          ->whereDoesntHave('appointments', function ($q2) use ($slotStartTime, $slotEndTime) {
                              $q2->whereBetween('time', [$slotStartTime, $slotEndTime]);
                          });
                    });
                }
            });
        }
        
    
        // --- Sorting ---
        if ($request->filled('sort_by')) {
            switch ($request->sort_by) {
                case 'price_low_high':
                    $doctors->orderBy('price', 'asc');
                    break;
                case 'price_high_low':
                    $doctors->orderBy('price', 'desc');
                    break;
                case 'rating_high_low':
                    $doctors->orderBy('rating', 'desc');
                    break;
                case 'experience_high_low':
                    $doctors->orderBy('experience', 'desc');
                    break;
                default:
                    $doctors->orderBy('created_at', 'desc');
            }
        } else {
            $doctors->orderBy('created_at', 'desc');
        }
    
        // --- Get results ---
        $doctors = $doctors->get()->map(function ($doctor) {
            $doctor->categories = is_string($doctor->categories) 
                ? json_decode($doctor->categories, true) 
                : ($doctor->categories ?? []);
            $doctor->availability = is_string($doctor->availability) 
                ? json_decode($doctor->availability, true) 
                : ($doctor->availability ?? []);
    
            $doctor->avg_rating = $doctor->reviews->count() 
                ? $doctor->reviews->avg('rating') 
                : ($doctor->rating ?? 0);
    
            return $doctor;
        });
    
        // --- AJAX response ---
        if ($request->ajax()) {
            try {
                $html = view('partials.doctor_cards', compact('doctors'))->render();
    
                return response()->json([
                    'success' => true,
                    'html' => $html,
                    'count' => $doctors->count(),
                    'message' => $doctors->count() > 0 ? 'Doctors loaded successfully' : 'No doctors found'
                ]);
            } catch (\Exception $e) {
                \Log::error('AJAX Error in doctors method: ' . $e->getMessage());
    
                return response()->json([
                    'success' => false,
                    'message' => 'Error loading doctors: ' . $e->getMessage(),
                    'html' => '<div class="col-12 text-center text-danger"><p>Error loading doctors. Please try again.</p></div>',
                    'count' => 0
                ], 500);
            }
        }
    
        // --- Regular page load ---
        return view('doctors', compact('doctors'));
    }
    public function doctor($id)
    {
        $doctor = Doctor::with(['reviews' => function($query) {
            $query->with(['user' => function($q) {
                $q->select('id', 'user_name');
            }]);
        }])->findOrFail($id);
        $adminBankDetail = AdminBankDetail::first();

        return view('doctor', compact('doctor', 'adminBankDetail'));
    }

    public function getBookedSlots(Request $request)
{
    $request->validate([
        'date' => 'required|date',
        'doctor_id' => 'required|exists:doctors,id'
    ]);

    $bookedSlots = Appointment::where('doctor_id', $request->doctor_id)
        ->where('date', $request->date)
        ->pluck('time')
        ->toArray();

    return response()->json([
        'booked_slots' => $bookedSlots
    ]);
}

public function bookAppointment(Request $request)
{
    
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email',
        'gender' => 'required|in:male,female,child',
        'date' => 'required|date|after_or_equal:today',
        'time' => 'required',
        'doctor_id' => 'required|exists:users,id',
        'bank_account_holder_name' => 'required|string|max:255',
        'bank_name' => 'required|string|max:255',
        'bank_account_number' => 'required|string|max:255',
        'payment_invoice' => 'required|file|max:5120',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    $existingUser = AppUser::where('user_email', $request->email)->first();

    $user = null;
if(!$existingUser){
    $user = AppUser::create([
        'user_name' => $request->name,
        'user_email' => $request->email,
        'user_password' => Hash::make(12345678),
    ]);
}
   


    $invoicePath = null;
    if ($request->hasFile('payment_invoice')) {
        $invoicePath = $request->file('payment_invoice')->store('appointment_invoices', 'public');
    }

    Appointment::create([
        'user_id' => $existingUser ? $existingUser->id : $user->id,
        'doctor_id' => $request->doctor_id,
        'date' => $request->date,
        'time' => $request->time,
        'status' => 'pending',
        'payment_intent_id' => null,
        'bank_account_holder_name' => $request->bank_account_holder_name,
        'bank_name' => $request->bank_name,
        'bank_account_number' => $request->bank_account_number,
        'payment_invoice_path' => $invoicePath,
    ]);

    return redirect()->back()->with('success', 'Appointment booked successfully!');
}
    public function contact()
    {
        return view('contact');
    }
    public function contactStore(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'comments' => 'required|string|max:1000',
        ]);
    
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }
    
        // Save to DB
        Contact::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'description' => $request->comments,
        ]);
    
        if ($request->ajax()) {
            return response()->json(['success' => 'Your message has been sent successfully!']);
        } else {
            return redirect()->back()->with('success', 'Your message has been sent successfully!');
        }
    }
    public function doctorCategoryWise($name)
{
    $doctors = Doctor::whereJsonContains('categories', $name)
                     ->orWhereJsonContains('specialities', $name)
                     ->get();

    // Check if AJAX request
    if (request()->ajax()) {
        $html = view('partials.doctor_cards', compact('doctors'))->render();
        return response()->json([
            'success' => true,
            'html' => $html,
            'count' => $doctors->count(),
        ]);
    }

    return view('doctors', compact('doctors'));
}

}
