<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class PostDeleteTest extends PostBaseTest
{

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
}
