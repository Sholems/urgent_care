<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Doctor;
use App\Models\AppUser;
use App\Models\Appointment;

class DoctorPatientsSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_patients_can_be_filtered_by_search_term(): void
    {
        $this->withoutMiddleware();

        $doctor = Doctor::create([
            'name' => 'Dr. Who',
            'photo' => 'photo.jpg',
            'categories' => [],
            'biography' => 'bio',
            'specialities' => [],
            'price' => 100,
        ]);

        $john = AppUser::create([
            'user_name' => 'John Doe',
            'user_email' => 'john@example.com',
            'user_password' => 'secret',
            'user_phone_number' => '1234567890',
        ]);

        $jane = AppUser::create([
            'user_name' => 'Jane Smith',
            'user_email' => 'jane@example.com',
            'user_password' => 'secret',
            'user_phone_number' => '5551234',
        ]);

        Appointment::create([
            'date' => '2024-05-10',
            'time' => '10:00',
            'user_id' => $john->id,
            'doctor_id' => $doctor->id,
            'status' => 'accepted',
        ]);

        Appointment::create([
            'date' => '2024-05-11',
            'time' => '11:00',
            'user_id' => $jane->id,
            'doctor_id' => $doctor->id,
            'status' => 'accepted',
        ]);

        $response = $this->getJson('/api/doctors/' . $doctor->id . '/patients?search=Jane');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'patients');
        $this->assertEquals('Jane Smith', $response->json('patients.0.user_name'));

        $response = $this->getJson('/api/doctors/' . $doctor->id . '/patients?search=john@example.com');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'patients');
        $this->assertEquals('john@example.com', $response->json('patients.0.user_email'));

        $response = $this->getJson('/api/doctors/' . $doctor->id . '/patients?search=5551234');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'patients');
        $this->assertEquals('5551234', $response->json('patients.0.user_phone_number'));
    }
}
