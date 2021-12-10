<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExploreController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\UCenterController;
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

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/social-login', [AuthController::class, 'socialiteLogin']);
    Route::post('/social-bind', [AuthController::class, 'socialiteBind']);
    Route::post('/social-register', [AuthController::class, 'socialiteRegister']);
});

Route::prefix('ucenter')->middleware('auth:sanctum')->group(function () {
    Route::any('/notifications', [UCenterController::class, 'notifications']);
    Route::put('/information', [UCenterController::class, 'information']);
    Route::put('/privacy', [UCenterController::class, 'privacy']);
});

Route::prefix('explore')->group(function () {
    Route::any('/index', [ExploreController::class, 'index']);
});

Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{news}', [NewsController::class, 'show']);




