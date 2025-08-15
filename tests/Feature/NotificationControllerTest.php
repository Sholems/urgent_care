<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_notifications_for_app_user_with_basename_type()
    {
        $user = AppUser::create([
            'user_name' => 'John Doe',
            'user_email' => 'john@example.com',
            'user_password' => bcrypt('secret'),
        ]);

        Notification::create([
            'id' => (string) Str::uuid(),
            'type' => 'Test',
            'notifiable_type' => 'AppUser',
            'notifiable_id' => $user->id,
            'data' => json_encode(['message' => 'hello']),
        ]);

        $response = $this->getJson("/api/notifications/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}

