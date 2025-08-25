<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\AppUser;

class AppUserUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_information(): void
    {
        $this->withoutMiddleware();

        $user = AppUser::create([
            'user_name' => 'Original Name',
            'user_email' => 'original@example.com',
            'user_password' => 'secret',
        ]);

        $payload = [
            'user_name' => 'Updated Name',
            'user_location' => 'New City',
            'medical_history' => 'None',
            'insurance_details' => 'Provider A',
        ];

        $response = $this->postJson('/api/user/update/' . $user->id, $payload);
        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'User updated successfully',
                     'user' => [
                         'id' => $user->id,
                         'user_name' => 'Updated Name',
                         'user_location' => 'New City',
                         'medical_history' => 'None',
                         'insurance_details' => 'Provider A',
                     ],
                 ]);

        $this->assertDatabaseHas('app_users', [
            'id' => $user->id,
            'medical_history' => 'None',
            'insurance_details' => 'Provider A',
        ]);
    }
}
