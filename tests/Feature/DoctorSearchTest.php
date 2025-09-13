<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Doctor;
use App\Models\AppUser;
use App\Models\Appointment;
use App\Models\Review;

class DoctorSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctors_can_be_sorted_by_distance(): void
    {
        $this->withoutMiddleware();

        Doctor::create([
            'name' => 'Dr Near',
            'photo' => 'photo.jpg',
            'categories' => [],
            'biography' => 'bio',
            'specialities' => [],
            'price' => 100,
            'latitude' => 0,
            'longitude' => 0,
        ]);

        Doctor::create([
            'name' => 'Dr Far',
            'photo' => 'photo.jpg',
            'categories' => [],
            'biography' => 'bio',
            'specialities' => [],
            'price' => 100,
            'latitude' => 10,
            'longitude' => 10,
        ]);

        $response = $this->getJson('/api/doctors/search?latitude=0&longitude=0&sort=distance');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Dr Near', $data[0]['name']);
        $this->assertLessThan($data[1]['distance'], $data[0]['distance']);
    }

    public function test_category_search_can_return_top_doctors(): void
    {
        $this->withoutMiddleware();

        $user = AppUser::create([
            'user_name' => 'John',
            'user_email' => 'john@example.com',
            'user_password' => 'secret',
            'user_phone_number' => '1234567890',
        ]);

        $doc1 = Doctor::create([
            'name' => 'Dr Average',
            'photo' => 'photo.jpg',
            'categories' => ['cardiology'],
            'biography' => 'bio',
            'specialities' => [],
            'price' => 100,
        ]);

        $doc2 = Doctor::create([
            'name' => 'Dr Top',
            'photo' => 'photo.jpg',
            'categories' => ['cardiology'],
            'biography' => 'bio',
            'specialities' => [],
            'price' => 100,
        ]);

        $appt1 = Appointment::create([
            'date' => '2024-05-10',
            'time' => '10:00',
            'user_id' => $user->id,
            'doctor_id' => $doc1->id,
            'status' => 'accepted',
        ]);
        Review::create([
            'appointment_id' => $appt1->id,
            'user_id' => $user->id,
            'doctor_id' => $doc1->id,
            'rating' => 3,
        ]);

        $appt2 = Appointment::create([
            'date' => '2024-05-11',
            'time' => '11:00',
            'user_id' => $user->id,
            'doctor_id' => $doc2->id,
            'status' => 'accepted',
        ]);
        Review::create([
            'appointment_id' => $appt2->id,
            'user_id' => $user->id,
            'doctor_id' => $doc2->id,
            'rating' => 5,
        ]);

        $response = $this->getJson('/api/doctors/category/cardiology?top=1');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Dr Top', $response->json('data.0.name'));
    }
}

