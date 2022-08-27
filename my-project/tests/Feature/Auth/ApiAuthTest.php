<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_UserRegistration()
    {
        $response = $this->post('/api/register', UserUtility::payload());
        $response->assertCreated();
    }

    public function test_UserLoginFailed()
    {
        $user = UserUtility::user();

        $response = $this->post('/api/login', [
            'email' => $user->email,
            'password' => "wrongPassword"
        ]);

        $response->assertUnauthorized();
    }


    public function test_UserLogin()
    {
        $user = UserUtility::user();

        $response = $this->post('/api/login', [
            'email' => $user->email,
            'password' => UserUtility::PASSWORD
        ]);

        $response->assertCreated();
    }

    public function test_UpdatePasswordAndLogin()
    {
        $user = UserUtility::user();
        $token = UserUtility::accessToken($user);

        $newPassword = 'NewPassword';
        $reponse = UserUtility::authApiRequest($this, '/users/' . $user->id, $token, 'PUT', [
            'password' => $newPassword
        ]);

        $reponse->assertOk();

        $reponse = $this->post('/api/login', [
            'email' => $user->email,
            'password' => $newPassword
        ]);

        $reponse->assertCreated();
        $reponse = $this->post('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $reponse->assertUnauthorized();
    }
}
