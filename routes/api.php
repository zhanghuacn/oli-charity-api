<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CharityController;
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


Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/social-login', [AuthController::class, 'socialiteLogin']);
Route::post('/auth/social-bind', [AuthController::class, 'socialiteBind']);
Route::post('/auth/social-register', [AuthController::class, 'socialiteRegister']);

Route::get('/explore/index', [ExploreController::class, 'index']);

Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{news}', [NewsController::class, 'show']);

Route::get('/charities', [CharityController::class, 'index']);
Route::get('/charities/{charity}', [CharityController::class, 'show']);

Route::get('/events', [ActivityController::class, 'index']);
Route::get('/events/{activity}', [ActivityController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/ucenter/notifications', [UCenterController::class, 'notifications']);
    Route::put('/ucenter/information', [UCenterController::class, 'information']);
    Route::put('/ucenter/privacy', [UCenterController::class, 'privacy']);

    Route::post('/events/{activity}/actions/follow', [ActivityController::class, 'subscribe']);
    Route::post('/events/{activity}/actions/unfollow', [ActivityController::class, 'unsubscribe']);

});













