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
}
