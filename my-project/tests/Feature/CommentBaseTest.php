<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CommentBaseTest extends TestCase
{
    use RefreshDatabase;
    const ENDPOINT = '/comments';


    protected function getComments(string $commentId = ''): TestResponse
    {
        return $this->get('/api' . $this::ENDPOINT . '/' . $commentId, [
            'Accept' => 'application/json',
        ]);
    }

    protected function createComment(string $accessToken, array $payload = []): TestResponse
    {

        $post = Post::all()->first();

        if (Post::count() == 0) {
            $this->actingAs(UserUtility::user());
            $post = Post::factory()->create();
        }

        if (count($payload) == 0) {
            $payload = [
                'post_id' => $post->id,
                'content' => fake()->text(50),
            ];
        }

        return UserUtility::authApiRequest($this, $this::ENDPOINT, $accessToken, 'POST', $payload);
    }

    protected function createCommentAsUser(): Comment
    {
        $user = UserUtility::user();
        $this->actingAs($user);

        $comment = Comment::factory()->create();
        Auth::logout();

        return $comment;
    }

    public function test_createComment()
    {

        $token = UserUtility::accessToken();

        $response = $this->createComment($token);
        $response->assertCreated();
    }

    public function test_updateComment()
    {
        $user = UserUtility::user();
        $token = UserUtility::accessToken($user);
        $this->actingAs($user);

        $comment = Comment::factory()->create();
        $comment->user_id;

        $content = 'my new comment';
        $payload = ['content' => $content];

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/' . $comment->id, $token, 'PUT', $payload);

        $response->assertOk();

        $commentData = $response->json();
        $this->assertEquals($content, $commentData['content']);
    }

    public function test_getAllComments()
    {
        $commentCount = 10;
        $user = UserUtility::user();
        $this->actingAs($user);

        Comment::factory($commentCount)->create();

        $response = $this->getComments();

        $response->assertOk();

        $commentData = $response->json('data');

        $this->assertEquals($commentCount, count($commentData));
    }


    public function test_maxCommentLength()
    {
        $maxSubjectLength = 255;

        $token = UserUtility::accessToken();

        $user = UserUtility::user();
        $this->actingAs($user);
        $post = Post::factory()->create();

        $payload = [
            'post_id' => $post->id,
            'content' => fake()->text($maxSubjectLength * 2),
        ];

        $response = $this->createComment($token, $payload);

        $response->assertUnprocessable();
    }

    public function test_getSingleComment()
    {
        $user = UserUtility::user();
        $this->actingAs($user);
        $comment = Comment::factory()->create();

        $response = $this->getComments($comment->id);

        $response->assertOk();

        $commentData = $response->json();

        $this->assertEquals($comment->id, $commentData['id']);
    }

    public function test_updateCommentAsNonUserUnauthorized()
    {

        $comment = $this->createCommentAsUser();
        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/' . $comment->id, '', 'PUT');
        $response->assertUnauthorized();
    }
}
