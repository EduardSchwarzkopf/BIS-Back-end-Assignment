<?php

namespace Tests\Feature;

use App\Models\User;

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

    static public function accessToken(): string
    {
        $user = UserUtility::user();
        return UserUtility::getAccessToken($user);
    }

    static public function adminAccessToken(): string
    {
        $user = UserUtility::admin();
        return UserUtility::getAccessToken($user);
    }

    static private function getAccessToken(User $user): string
    {
        return $user->createToken('access_token')->plainTextToken;
    }
}
