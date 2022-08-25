<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_UserLoginSuccessfull()
    {
        $user = User::factory()->create();

        $response = $this->post('/api/login', [
            'email' => $user->email,
            'password' => "password"
        ]);

        $response->assertStatus(201);
    }

    public function test_UserLoginFailed()
    {
        $user = User::factory()->create();

        $response = $this->post('/api/login', [
            'email' => $user->email,
            'password' => "wrongPassword"
        ]);

        $response->assertStatus(401);
    }
}
