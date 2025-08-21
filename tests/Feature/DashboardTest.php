<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Doctor;
use App\Models\AppUser;
use App\Models\Appointment;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_dashboard_returns_monthly_earnings(): void
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

        $user = AppUser::create([
            'user_name' => 'John',
            'user_email' => 'john@example.com',
            'user_password' => 'secret',
        ]);

        Appointment::create([
            'date' => '2024-05-10',
            'time' => '10:00',
            'user_id' => $user->id,
            'doctor_id' => $doctor->id,
            'status' => 'accepted',
        ]);
        Appointment::create([
            'date' => '2024-06-12',
            'time' => '11:00',
            'user_id' => $user->id,
            'doctor_id' => $doctor->id,
            'status' => 'accepted',
        ]);

        $response = $this->getJson('/api/dashboard/doctor/' . $doctor->id);
        $response->assertStatus(200);
        $response->assertJson([
            'totalEarnings' => 200.0,
        ]);
        $response->assertJsonFragment(['month' => '2024-05', 'earnings' => 100.0]);
        $response->assertJsonFragment(['month' => '2024-06', 'earnings' => 100.0]);
    }

    public function test_admin_dashboard_returns_monthly_revenue(): void
    {
        $this->withoutMiddleware();

        $doctor1 = Doctor::create([
            'name' => 'Dr. A',
            'photo' => 'a.jpg',
            'categories' => [],
            'biography' => 'bio',
            'specialities' => [],
            'price' => 100,
        ]);

        $doctor2 = Doctor::create([
            'name' => 'Dr. B',
            'photo' => 'b.jpg',
            'categories' => [],
            'biography' => 'bio',
            'specialities' => [],
            'price' => 200,
        ]);

        $user = AppUser::create([
            'user_name' => 'Jane',
            'user_email' => 'jane@example.com',
            'user_password' => 'secret',
        ]);

        Appointment::create([
            'date' => '2024-05-05',
            'time' => '09:00',
            'user_id' => $user->id,
            'doctor_id' => $doctor1->id,
            'status' => 'accepted',
        ]);
        Appointment::create([
            'date' => '2024-05-15',
            'time' => '09:30',
            'user_id' => $user->id,
            'doctor_id' => $doctor2->id,
            'status' => 'accepted',
        ]);
        Appointment::create([
            'date' => '2024-06-20',
            'time' => '14:00',
            'user_id' => $user->id,
            'doctor_id' => $doctor1->id,
            'status' => 'accepted',
        ]);

        $response = $this->getJson('/api/dashboard/admin');
        $response->assertStatus(200);
        $response->assertJson([
            'totalRevenue' => 400.0,
        ]);
        $response->assertJsonFragment(['month' => '2024-05', 'revenue' => 300.0]);
        $response->assertJsonFragment(['month' => '2024-06', 'revenue' => 100.0]);
    }
}
