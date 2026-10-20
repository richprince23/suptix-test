<?php

namespace Tests\Feature\Api\V2;

use App\Models\User;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function test_user_can_register()
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '+233201234567',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v2/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                    ],
                    'token',
                ],
            ]);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v2/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                    ],
                    'token',
                ],
            ]);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $response = $this->postJson('/api/v2/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Invalid credentials',
            ]);
    }

    public function test_honeypot_protection_in_register()
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'honeypot' => 'bot-filled-value',
        ];

        $response = $this->postJson('/api/v2/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Validation failed',
            ])
            ->assertJsonValidationErrors('honeypot');
    }

    public function test_honeypot_protection_in_login()
    {
        $response = $this->postJson('/api/v2/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'honeypot' => 'bot-filled-value',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Validation failed',
            ])
            ->assertJsonValidationErrors('honeypot');
    }
}