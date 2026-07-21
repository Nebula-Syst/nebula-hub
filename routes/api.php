<?php

use App\Http\Controllers\Api\AdminLinkController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ButtonController;
use App\Http\Controllers\Api\LinkController;
use App\Http\Controllers\Api\LinkTypeController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Nebula Hub API — see /api/documentation for the full Swagger UI.

Route::post('/login', [AuthController::class, 'login']);

// Public, read-only.
Route::get('/pages', [PageController::class, 'show']);

Route::middleware('api.token')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/links', [LinkController::class, 'index']);
    Route::post('/links', [LinkController::class, 'store']);
    Route::post('/links/reorder', [LinkController::class, 'reorder']);
    Route::get('/links/{link}', [LinkController::class, 'show']);
    Route::put('/links/{link}', [LinkController::class, 'update']);
    Route::delete('/links/{link}', [LinkController::class, 'destroy']);

    Route::get('/buttons', [ButtonController::class, 'index']);
    Route::get('/buttons/{button}', [ButtonController::class, 'show']);

    Route::get('/link-types', [LinkTypeController::class, 'index']);

    Route::middleware('api.admin')->group(function () {
        Route::apiResource('users', UserController::class);

        Route::prefix('users/{user}/links')->group(function () {
            Route::get('/', [AdminLinkController::class, 'index']);
            Route::post('/', [AdminLinkController::class, 'store']);
            Route::post('/reorder', [AdminLinkController::class, 'reorder']);
            Route::get('/{link}', [AdminLinkController::class, 'show']);
            Route::put('/{link}', [AdminLinkController::class, 'update']);
            Route::delete('/{link}', [AdminLinkController::class, 'destroy']);
        });

        Route::post('/buttons', [ButtonController::class, 'store']);
        Route::put('/buttons/{button}', [ButtonController::class, 'update']);
        Route::delete('/buttons/{button}', [ButtonController::class, 'destroy']);

        Route::put('/pages', [PageController::class, 'update']);
    });
});
