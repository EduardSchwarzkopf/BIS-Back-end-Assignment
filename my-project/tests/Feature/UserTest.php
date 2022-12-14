<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class UserTest extends TestCase
{

    use RefreshDatabase;

    const ENDPOINT = '/users';
    const ADMINKEY = 'is_admin';

    public function test_SetIsAdmin()
    {
        $token = UserUtility::adminAccessToken();

        $payload[$this::ADMINKEY] = true;

        $user =  User::factory()->create();

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/' . $user->id, $token, 'PUT', $payload);

        $response->assertOk();

        $responseData = $response->json('data');

        $this->assertEquals($payload[$this::ADMINKEY], $responseData[$this::ADMINKEY]);
    }

    public function test_SetIsAdminUnprocessable()
    {
        $user = UserUtility::user();
        $token = UserUtility::accessToken($user);

        $payload[$this::ADMINKEY] = true;

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/' . $user->id, $token, 'PUT', $payload);

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
            $payload[$this::ADMINKEY] = $type;

            $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/' . $user->id, $token, 'PUT', $payload);
            $response->assertUnprocessable();
        }
    }

    public function test_adminUpdatesUser()
    {
        $token = UserUtility::adminAccessToken();
        $user = UserUtility::user();

        $newName = 'NewName';
        $payload = ['name' => $newName];

        $response = $this->updateUser($token, $user, $payload);

        $response->assertOk();

        $responseData = $response->json('data');
        $this->assertEquals($newName, $responseData['name']);
    }

    public function test_updateUser()
    {
        $user = UserUtility::user();
        $token = UserUtility::accessToken($user);

        $newName = 'NewName';
        $payload = ['name' => $newName];

        $response = $this->updateUser($token, $user, $payload);

        $response->assertOk();

        $responseData = $response->json('data');
        $this->assertEquals($newName, $responseData['name']);
    }

    public function test_updateOtherUserForbidden()
    {
        $user = UserUtility::user();
        $adminUser = UserUtility::admin();

        $token = UserUtility::accessToken($user);

        $newName = 'NewName';
        $payload = ['name' => $newName];

        $response = $this->updateUser($token, $adminUser, $payload);

        $response->assertForbidden();
    }

    private function updateUser(string $accessToken, User $user, array $payload = []): TestResponse
    {
        return UserUtility::authApiRequest($this, $this::ENDPOINT . '/' . $user->id, $accessToken, 'PUT', $payload);
    }

    public function test_generateNickname()
    {
        $surname = 'Depp';
        $name = 'Johnny';

        $payload = UserUtility::payload();
        $payload['email'] = 'nickname@test.de';
        $payload['name'] = $name;
        $payload['meta_data']['surname'] = $surname;


        $response = $this->post('/api/register', $payload);

        $response->assertCreated();
        $responseData = $response->json('data');
        $responseNickname = $responseData['meta_data']['nickname'];

        $expectedNickname = strtolower($surname . substr($name, 0, 3));
        $this->assertEquals($expectedNickname, $responseNickname);
    }

    public function test_deleteSelf()
    {
        $user = UserUtility::user();
        $token = UserUtility::accessToken($user);

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/' . $user->id, $token, 'DELETE');

        $response->assertNoContent();
    }

    public function test_adminDeleteUser()
    {
        $user = UserUtility::user();
        $token = UserUtility::adminAccessToken();

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/' . $user->id, $token, 'DELETE');
        $response->assertNoContent();
    }

    public function test_userDeleteOtherForbidden()
    {
        $user = UserUtility::admin();
        $token = UserUtility::accessToken();

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/' . $user->id, $token, 'DELETE');
        $response->assertForbidden();
    }
}
