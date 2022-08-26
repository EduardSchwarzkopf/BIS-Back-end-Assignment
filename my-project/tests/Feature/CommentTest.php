<?php

namespace Tests\Feature;


use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;
    const ENDPOINT = '/comments';


    protected function getComments(string $commentId = ''): TestResponse
    {
        return $this->get('/api' . $this::ENDPOINT . '/' . $commentId, [
            'Accept' => 'application/json',
        ]);
    }

    protected function createComment(User $user, array $payload = []): TestResponse
    {

        $post = Post::all()->first();

        if (Post::count() == 0) {
            $this->actingAs($user);
            $post = Post::factory()->create();
        }

        if (count($payload) == 0) {
            $payload = [
                'post_id' => $post->id,
                'content' => fake()->text(50),
            ];
        }

        return UserUtility::authApiRequest($this, $this::ENDPOINT, UserUtility::accessToken($user), 'POST', $payload);
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

        $response = $this->createComment(UserUtility::user());
        $response->assertCreated();
    }

    public function test_updateComment()
    {
        $user = UserUtility::user();
        $token = UserUtility::accessToken($user);
        $this->actingAs($user);

        $comment = Comment::factory()->create();

        $content = 'my new comment';
        $payload = ['content' => $content];

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/' . $comment->id, $token, 'PUT', $payload);

        $response->assertOk();

        $commentData = $response->json();
        $this->assertEquals($content, $commentData['content']);
    }



    public function test_updateCommentForbidden()
    {
        $admin = UserUtility::admin();
        $this->actingAs($admin);

        $comment = Comment::factory()->create();

        $content = 'my new comment';
        $payload = ['content' => $content];

        $user = UserUtility::user();
        $this->actingAs($user);
        $token = UserUtility::accessToken($user);
        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/' . $comment->id, $token, 'PUT', $payload);

        $response->assertForbidden();
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
        $maxContentLength = 255;

        $token = UserUtility::accessToken();

        $user = UserUtility::user();
        $this->actingAs($user);
        $post = Post::factory()->create();

        $payload = [
            'post_id' => $post->id,
            'content' => fake()->regexify('[A-Za-z0-9]{' . $maxContentLength + 1 . '}'),
        ];

        $response = $this->createComment($user, $payload);

        $response->assertUnprocessable();

        $message = $response->json('message');

        $this->assertTrue(str_contains($message, $maxContentLength));
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

    public function test_deleteComment()
    {
        $comment = $this->createCommentAsUser();

        $user = UserUtility::user();
        $this->actingAs($user);

        $reponse = UserUtility::authApiRequest($this, $this::ENDPOINT . '/' . $comment->id, UserUtility::accessToken($user), 'DELETE');

        $reponse->assertNoContent();

        $this->assertEquals(0, Comment::count());
        $this->assertEquals(1, Comment::withTrashed()->count());
    }

    public function test_deleteCommentForbidden()
    {
        $commentResponse = $this->createComment(UserUtility::admin());
        $commentData = $commentResponse->json();
        $commentId = $commentData['id'];

        $user = UserUtility::user();
        $this->actingAs($user);

        $reponse = UserUtility::authApiRequest($this, $this::ENDPOINT . '/' . $commentId, UserUtility::accessToken($user), 'DELETE');

        $reponse->assertForbidden();
    }
}
