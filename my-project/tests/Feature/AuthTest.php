<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_UserRegistration()
    {
        $response = $this->post('/api/register', UserUtility::payload());
        $response->assertCreated();
    }

    public function test_UserLoginFailed()
    {
        $response = $this->post('/api/login', [
            'email' => UserUtility::EMAIL,
            'password' => "wrongPassword"
        ]);

        $response->assertUnauthorized();
    }


    public function test_UserLogin()
    {

        $loginResponse = $this->post('/api/login', [
            'email' => UserUtility::EMAIL,
            'password' => UserUtility::PASSWORD
        ]);

        $loginResponse->assertCreated();
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
