<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [PostController::class, "List"]);

Route::get('/post/{d}', [PostController::class, "Show"]);

Route::get('/post/{d}/comments', [PostController::class, "ListComments"]);

Route::get('/token', function () {
    return response() -> json(["token" => csrf_token()]);
});

Route::post('/create', [PostController::class, "Create"]);

Route::get('/delete/{d}', [PostController::class, "Delete"]);

Route::post('/update/{d}', [PostController::class, "Update"]);

Route::post('/comment/{d}', [PostController::class, "Comment"]);