<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Doctor;
use App\Models\AppUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AppointmentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_defaults_status_to_pending(): void
    {
        $this->withoutMiddleware();

        $doctor = Doctor::create([
            'name' => 'Dr. Smith',
            'photo' => 'doctor.jpg',
            'categories' => [],
            'biography' => 'Experienced doctor',
            'specialities' => [],
            'price' => 150,
        ]);

        $user = AppUser::create([
            'user_name' => 'Jane Doe',
            'user_email' => 'jane@example.com',
            'user_password' => 'secret',
        ]);

        $response = $this->postJson('/api/appointments/create', [
            'date' => '2024-07-01',
            'time' => '09:00',
            'user_id' => $user->id,
            'doctor_id' => $doctor->id,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('appointments', [
            'user_id' => $user->id,
            'doctor_id' => $doctor->id,
            'status' => 'pending',
        ]);
    }
}
