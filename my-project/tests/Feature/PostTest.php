<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;
    const ENDPOINT = '/posts';

    public function test_createPost()
    {

        $token = UserUtility::accessToken();

        $payload = [
            'subject' => fake()->text(20),
            'description' => fake()->text(50),
            'content' => fake()->text(300),
        ];

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT, $token, 'POST', $payload);
        $response->assertCreated();
    }
}
