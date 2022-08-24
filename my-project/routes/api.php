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


// Public routes
Route::post('/login', [ApiAuthController::class, 'login']);
Route::post('/users/', [UserController::class, 'index']);


// Protected Routes
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('/logout', [ApiAuthController::class, 'logout'])->middleware('auth:web');
    Route::apiResource('users', UserController::class);
    Route::apiResource('posts', PostController::class);
    Route::apiResource('posts', PostController::class);
    Route::get('/posts/restore/{id}', [PostController::class, 'restore']);
    Route::get('/posts/search/{subject}', [PostController::class, 'search']);
    Route::delete('/posts/forcedelete/{id}', [PostController::class, 'forcedelete']);
    Route::apiResource('comments', CommentController::class);
    Route::get('/comments/restore/{id}', [CommentController::class, 'restore']);
    Route::delete('/comments/forcedelete/{id}', [CommentController::class, 'forcedelete']);
});
