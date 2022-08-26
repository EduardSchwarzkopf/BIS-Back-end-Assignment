<?php

namespace Tests\Feature;

use App\Models\Post;
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

    public function test_updatePost()
    {
        $post = Post::factory()->create();

        $subject = 'New Subject';
        $payload = ['subject' => $subject];

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/' . $post->id, UserUtility::accessToken(), 'PUT', $payload);

        $response->assertOk();

        $postData = $response->json();
        $this->assertEquals($subject, $postData['subject']);
    }

    public function test_getAllPosts()
    {
        $postCount = 10;
        $post = Post::factory($postCount)->create();

        $response = $this->get('/api' . $this::ENDPOINT, [
            'Accept' => 'application/json',
        ]);

        $response->assertOk();

        $postData = $response->json();

        $this->assertEquals($postCount, count($postData['data']));
    }

    public function test_getSinglePost()
    {
        $post = Post::factory()->create();

        $response = $this->get('/api' . $this::ENDPOINT . '/' . $post->id, [
            'Accept' => 'application/json',
        ]);

        $response->assertOk();

        $postData = $response->json();

        $this->assertEquals($post->id, $postData['id']);
    }

    public function test_maxSubjectLength()
    {
        $maxSubjectLength = 64;

        $token = UserUtility::accessToken();

        $payload = [
            'subject' => fake()->text(200),
            'description' => fake()->text(50),
            'content' => fake()->text(300),
        ];

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT, $token, 'POST', $payload);

        $response->assertUnprocessable();

        $message = $response->json('message');

        $this->assertTrue(str_contains($message, $maxSubjectLength));
    }
}
