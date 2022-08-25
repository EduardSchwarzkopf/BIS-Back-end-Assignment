<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    private $adminKey = 'is_admin';

    public function test_SetIsAdmin()
    {
        $token = UserUtility::adminAccessToken();

        $payload[$this->adminKey] = true;

        $user =  User::factory()->create();

        $response = UserUtility::authApiRequest($this, '/users/' . $user->id, $token, 'PUT', $payload);

        $response->assertOk();

        $responseData = $response->json()['data'];

        $this->assertEquals($payload[$this->adminKey], $responseData[$this->adminKey]);
    }

    public function test_SetIsAdminFail()
    {
        $user = UserUtility::user();
        $token = UserUtility::accessToken($user);

        $payload[$this->adminKey] = true;

        $response = UserUtility::authApiRequest($this, '/users/' . $user->id, $token, 'PUT', $payload);

        $response->assertUnprocessable();
    }

    public function test_SetIsAdminWrongType()
    {
        $user = UserUtility::admin();
        $token = UserUtility::accessToken($user);

        $typeList = [
            'string',
            123,
            3.14,
            [1, 2, 3]
        ];

        foreach ($typeList as $type) {
            $payload[$this->adminKey] = $type;

            $response = UserUtility::authApiRequest($this, '/users/' . $user->id, $token, 'PUT', $payload);
            $response->assertUnprocessable();
        }
    }
}
