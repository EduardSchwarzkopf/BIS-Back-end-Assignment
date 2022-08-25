<?php

namespace Tests\Feature;


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
}
