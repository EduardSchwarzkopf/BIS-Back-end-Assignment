<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', Post::class);

        // make newest on top
        return Post::orderBy('created_at', 'desc')->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostRequest $request)
    {
        $this->authorize('create', Post::class);
        $this->handleDescription($request);
        return Post::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        $this->authorize('view', Post::class);
        return $post;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(PostRequest $request, Post $post)
    {
        $this->authorize('update', Post::class);
        $this->handleDescription($request);
        $post->update($request->all());
        return $post;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        $post->delete();
        return response()->noContent();
    }

    /**
     * Search by subject
     *
     * @param  str $subject
     * @return \Illuminate\Http\Response
     */
    public function search($subject)
    {
        $this->authorize('view', Post::class);
        $postList = Post::where('subject', 'like', '%' . $subject . '%')->get();
        return PostResource::collection($postList);
    }

    /**
     * handle description
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    private function handleDescription(PostRequest &$request): void
    {

        if (empty($request->description)) {
            $request->merge(
                ['description' => substr($request->content, 0, 155)]
            );
        }
    }

    /**
     * Display the specified trashed resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function trashedShow(int $id)
    {
        $this->authorize('viewTrashed', Post::class);
        return  Post::onlyTrashed()->find($id);
    }

    /**
     * Display a listing of the trashed resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function trashed()
    {
        $this->authorize('viewTrashed', Post::class);
        return  Post::onlyTrashed()->paginate(10);
    }

    /**
     * restore desired model
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function restore(int $id)
    {
        $this->authorize('restore', Post::class);
        $post = Post::withTrashed()->find($id)->restore();
        return $post;
    }

    /**
     * remove model permantly
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function forceDelete(int $id)
    {
        $this->authorize('forceDelete', Post::class);
        Post::onlyTrashed()->find($id)->forceDelete();
        return response()->noContent();
    }
}
