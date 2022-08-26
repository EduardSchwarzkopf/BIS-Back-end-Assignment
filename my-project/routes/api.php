<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



// Protected Routes
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('/logout', [ApiAuthController::class, 'logout'])->middleware('auth:web');
    Route::apiResource('users', UserController::class);
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{post}', [PostController::class, 'update']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
    Route::get('/posts/restore/{id}', [PostController::class, 'restore']);
    Route::get('/posts/search/{subject}', [PostController::class, 'search']);
    Route::get('/posts/trashed/all/', [PostController::class, 'trashed']);
    Route::get('/posts/trashed/{id}', [PostController::class, 'trashedShow']);
    Route::delete('/posts/forcedelete/{id}', [PostController::class, 'forcedelete']);
    Route::apiResource('comments', CommentController::class);
    Route::get('/comments/restore/{id}', [CommentController::class, 'restore']);
    Route::get('/comments/trashed/all/', [CommentController::class, 'trashed']);
    Route::get('/comments/trashed/{id}', [CommentController::class, 'trashedShow']);
    Route::delete('/comments/forcedelete/{id}', [CommentController::class, 'forcedelete']);
});

// Public routes
Route::post('/login', [ApiAuthController::class, 'login']);
Route::post('/register', [UserController::class, 'store']);

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{post}', [PostController::class, 'show']);
