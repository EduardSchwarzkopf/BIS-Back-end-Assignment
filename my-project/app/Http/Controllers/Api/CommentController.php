<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Comment::orderBy('created_at', 'desc')->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CommentRequest $request)
    {
        return Comment::create($request->validate($request->rules()));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show(Comment $comment)
    {
        //
        return $comment;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(CommentRequest $request, Comment $comment)
    {
        $comment->update($request->validate($request->rules()));
        return $comment;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();
        return response()->noContent();
    }

    /**
     * Display the specified trashed resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function trashedShow(int $id)
    {
        $this->authorize('viewTrashed', Post::class);
        return  Comment::onlyTrashed()->find($id);
    }

    /**
     * Display a listing of the trashed resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function trashed()
    {
        $this->authorize('viewTrashed', Post::class);
        return  Comment::onlyTrashed()->paginate(10);
    }

    /**
     * restore desired model
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function restore(int $id)
    {
        $this->authorize('restore', Comment::class);
        $post = Comment::withTrashed()->find($id)->restore();
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
        $this->authorize('forceDelete', Comment::class);
        Comment::onlyTrashed()->find($id)->forceDelete();
        return response()->noContent();
    }
}
