<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::get('/post', [PostController::class, "List"]);
Route::get('/post/{d}', [PostController::class, "Show"]);
Route::get('/post/{d}/comments', [PostController::class, "ListComments"]);
Route::post('/post', [PostController::class, "Create"]);
Route::get('/post/delete/comment/{d}', [PostController::class, "DeleteComment"]);
Route::get('/post/delete/{d}', [PostController::class, "Delete"]);
Route::post('/post/update/{d}', [PostController::class, "Update"]);
Route::post('/post/comment/{d}', [PostController::class, "Comment"]);
Route::post('/post/like/{d}', [PostController::class, "Like"]);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
