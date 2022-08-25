<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class UserUtility
{

    const NAME = 'TestUser';
    const EMAIL = 'user@test.de';
    const PASSWORD = 'password';

    static public function payload()
    {
        return [
            'name' => UserUtility::NAME,
            'email' => UserUtility::EMAIL,
            'password' => UserUtility::PASSWORD,
            'meta_data' => [
                'surname' => 'Depp',
                'phone' => 'test_phone',
                'address' => 'test_address',
                'city' => 'test_city',
                'state' => 'test_state',
                'zip' => 'test_zip'
            ],
        ];
    }

    static public function user(): User
    {
        $user = User::where('is_admin', false)->first();

        if ($user == null) {
            $user = User::factory()->create();
        }

        return $user;
    }

    static public function admin(): User
    {
        $user = User::where('is_admin', true)->first();

        if ($user == null) {
            $user = User::factory()->create(['is_admin' => true]);
        }

        return $user;
    }

    static public function accessToken(User $user = null): string
    {
        $user = $user ? $user : UserUtility::user();
        return UserUtility::getAccessToken($user);
    }

    static public function adminAccessToken(User $user = null): string
    {
        $user = $user ? $user : UserUtility::admin();
        return UserUtility::getAccessToken($user);
    }

    static private function getAccessToken(User $user): string
    {
        return $user->createToken('access_token')->plainTextToken;
    }

    static public function authApiRequest(TestCase $test, string $endpoint, string $accessToken, string $method = 'GET', array $payload = []): TestResponse
    {
        $method = strtoupper($method);

        $response = null;
        $apiEndpoint = '/api' . $endpoint;
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => "Bearer $accessToken"
        ];

        switch ($method) {
            case 'GET':
                $response = $test->get($apiEndpoint, $headers);

            case 'POST':
                $response = $test->post($apiEndpoint, $payload, $headers);
                break;

            case 'PUT':
                $response = $test->put($apiEndpoint, $payload, $headers);
                break;

            case 'DELETE':
                $response = $test->delete($apiEndpoint, $payload, $headers);
                break;

            default:
                break;
        }

        return $response;
    }
}
