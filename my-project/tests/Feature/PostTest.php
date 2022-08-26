<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;
    const ENDPOINT = '/posts';


    private function getPosts(string $postId = ''): TestResponse
    {
        return $this->get('/api' . $this::ENDPOINT . '/' . $postId, [
            'Accept' => 'application/json',
        ]);
    }

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
        Post::factory($postCount)->create();

        $response = $this->getPosts();

        $response->assertOk();

        $postData = $response->json('data');

        $this->assertEquals($postCount, count($postData));
    }

    public function test_getSinglePost()
    {
        $post = Post::factory()->create();

        $response = $this->getPosts($post->id);

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


    public function test_expectedPostData()
    {
        $post = Post::factory()->create();

        $response = $this->getPosts($post->id);

        $data = $response->json();

        $this->assertEquals($post->subject, $data['subject']);
        $this->assertTrue((bool)strtotime($data['created_at'])); // is a valid date?
        $this->assertEquals($post->description, $data['description']);

        $user = User::find($post->user_id);
        $dataUser = $data['user'];
        $this->assertEquals($user->id, $dataUser['id']);
        $this->assertEquals($user->name, $dataUser['name']);
    }

    public function test_newestPostFirst()
    {

        $postList = Post::factory(10)->create();
        $postList = $postList->all();

        // sort by date desc
        usort($postList, fn ($a, $b) => strtotime($b["created_at"]) - strtotime($a["created_at"]));

        $response = $this->getPosts();

        $responsePostList = $response->json('data');

        for ($i = 0; $i < count($postList); $i++) {
            $post = $postList[$i];
            $responsePost = $responsePostList[$i];

            $this->assertEquals(strtotime($post['created_at']), strtotime($responsePost['created_at']));
        }
    }

    public function test_newestPostFirstFail()
    {
        $postList = Post::factory(10)->create([
            'created_at' => fake()->dateTimeThisMonth()
        ]);
        $postList = $postList->all();

        $response = $this->getPosts();

        $responsePostList = $response->json('data');

        for ($i = 0; $i < count($postList); $i++) {
            $post = $postList[$i];
            $responsePost = $responsePostList[$i];

            $this->assertEquals(strtotime($post['created_at']), strtotime($responsePost['created_at']));
        }
    }
}
