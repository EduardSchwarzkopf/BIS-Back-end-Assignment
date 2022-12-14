<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PostTestBase extends TestCase
{
    use RefreshDatabase;
    const ENDPOINT = '/posts';


    protected function getPosts(string $postId = ''): TestResponse
    {
        return $this->get('/api' . $this::ENDPOINT . '/' . $postId, [
            'Accept' => 'application/json',
        ]);
    }

    protected function createPost(string $accessToken, array $payload = []): TestResponse
    {

        if (count($payload) == 0) {
            $payload = [
                'subject' => fake()->text(20),
                'description' => fake()->text(50),
                'content' => fake()->text(300),
            ];
        }

        return UserUtility::authApiRequest($this, $this::ENDPOINT, $accessToken, 'POST', $payload);
    }

    protected function createPostAsUser(): Post
    {
        $user = UserUtility::user();
        $this->actingAs($user);

        $post = Post::factory()->create();
        Auth::logout();

        return $post;
    }

    public function test_createPost()
    {

        $token = UserUtility::accessToken();

        $response = $this->createPost($token);
        $response->assertCreated();
    }

    public function test_updatePost()
    {
        $user = UserUtility::user();
        $token = UserUtility::accessToken($user);
        $this->actingAs($user);

        $post = Post::factory()->create();
        $post->user_id;

        $subject = 'New Subject';
        $payload = ['subject' => $subject];

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/' . $post->id, $token, 'PUT', $payload);

        $response->assertOk();

        $postData = $response->json();
        $this->assertEquals($subject, $postData['subject']);
    }

    public function test_getAllPosts()
    {
        $postCount = 10;
        $user = UserUtility::user();
        $this->actingAs($user);

        Post::factory($postCount)->create();

        $response = $this->getPosts();

        $response->assertOk();

        $postData = $response->json('data');

        $this->assertEquals($postCount, count($postData));
    }

    public function test_getSinglePost()
    {
        $user = UserUtility::user();
        $this->actingAs($user);
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
            'subject' => fake()->regexify('[A-Za-z0-9]{' . $maxSubjectLength + 1 . '}'),
            'description' => fake()->text(50),
            'content' => fake()->text(300),
        ];

        $response = $this->createPost($token, $payload);

        $response->assertUnprocessable();

        $message = $response->json('message');

        $this->assertTrue(str_contains($message, $maxSubjectLength));
    }


    public function test_expectedPostData()
    {
        $user = UserUtility::user();
        $this->actingAs($user);
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

        $user = UserUtility::user();
        $this->actingAs($user);
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
        $user = UserUtility::user();
        $this->actingAs($user);
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

    public function test_updatePostAsNonUserUnauthorized()
    {

        $post = $this->createPostAsUser();
        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/' . $post->id, '', 'PUT');
        $response->assertUnauthorized();
    }

    public function test_deleteOwnPost()
    {
        $user = UserUtility::user();
        $token = UserUtility::accessToken($user);
        $this->actingAs($user);
        $post = Post::factory()->create();

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/' . $post->id, $token, 'DELETE');

        $response->assertNoContent();
    }

    public function test_adminDeleteOtherPosts()
    {
        $user = UserUtility::user();

        $this->actingAs($user);
        $post = Post::factory()->create();

        $admin = UserUtility::admin();
        $this->actingAs($admin);

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/' . $post->id, UserUtility::adminAccessToken($admin), 'DELETE');

        $response->assertNoContent();
    }

    public function test_deletePostAsNonUserUnauthorized()
    {
        $post = $this->createPostAsUser();

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/' . $post->id, '', 'DELETE');
        $response->assertUnauthorized();
    }

    public function test_getTrashedPosts()
    {
        $admin = UserUtility::admin();
        $this->actingAs($admin);

        $post = Post::factory()->create();
        $post->delete();

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/trashed/all', UserUtility::accessToken($admin));

        $response->assertOk();

        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    public function test_getSingleTrashedPost()
    {
        $admin = UserUtility::admin();
        $this->actingAs($admin);

        $post = Post::factory()->create();
        $post->delete();

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/trashed/' . $post->id, UserUtility::accessToken($admin));

        $response->assertOk();

        $data = $response->json();
        $this->assertEquals($post->id, $data['id']);
    }

    public function test_getTrashedPostsForbidden()
    {
        $post = $this->createPostAsUser();
        $post->delete();

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/trashed/all', UserUtility::accessToken());

        $response->assertForbidden();
    }

    public function test_getSingleTrashedPostForbidden()
    {
        $post = $this->createPostAsUser();
        $post->delete();

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/trashed/' . $post->id, UserUtility::accessToken());

        $response->assertForbidden();
    }

    public function test_deleteTrashedPost()
    {

        $user = UserUtility::admin();
        $this->actingAs($user);

        $post = Post::factory()->create();
        $post->delete();

        $admin = UserUtility::admin();
        $this->actingAs($admin);

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/trashed/' . $post->id, UserUtility::accessToken($admin), 'DELETE');

        $response->assertNoContent();
    }

    public function test_deleteTrashedPostForbidden()
    {

        $post = $this->createPostAsUser();
        $post->delete();

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/trashed/' . $post->id, UserUtility::accessToken(), 'DELETE');

        $response->assertForbidden();
    }

    public function test_restoreTrashedPost()
    {
        $user = UserUtility::admin();
        $this->actingAs($user);

        $post = Post::factory()->create();
        $post->delete();

        $this->assertCount(0, Post::all());

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/restore/' . $post->id, UserUtility::accessToken($user));

        $response->assertOk();
        $this->assertCount(1, Post::all());
    }


    public function test_restoreTrashedPostForbidden()
    {
        $post = $this->createPostAsUser();
        $post->delete();

        $this->assertCount(0, Post::all());

        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/restore/' . $post->id, UserUtility::accessToken());

        $response->assertForbidden();
        $this->assertCount(0, Post::all());
    }

    public function test_deleteOtherPostForbidden()
    {
        $admin = UserUtility::admin();
        $user = UserUtility::user();
        $token = UserUtility::accessToken($user);

        $this->actingAs($admin);
        $post = Post::factory()->create();

        $this->actingAs($user);
        $response = UserUtility::authApiRequest($this, $this::ENDPOINT . '/' . $post->id, $token, 'DELETE');

        $response->assertForbidden();
    }

    public function test_createPostAsNonUserUnauthorized()
    {
        $response = $this->createPost('');
        $response->assertUnauthorized();
    }

    public function test_autoPruneDeletedPosts()
    {
        $user = UserUtility::user();
        $this->actingAs($user);

        $post = Post::factory()->create();
        $post->delete();
        $post->create_at = now()->subHours(5);

        $this->artisan('model:prune');


        $this->assertEquals(0, Post::withTrashed()->count());
    }
}
