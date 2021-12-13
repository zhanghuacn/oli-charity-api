<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CharityController;
use App\Http\Controllers\Api\ExploreController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\UCenterController;
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


Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/social-login', [AuthController::class, 'socialiteLogin']);
Route::post('/auth/social-bind', [AuthController::class, 'socialiteBind']);
Route::post('/auth/social-register', [AuthController::class, 'socialiteRegister']);

Route::get('/ucenter/follow-charities', [UCenterController::class, 'followCharities']);
Route::get('/ucenter/follow-events', [UCenterController::class, 'followActivities']);
Route::get('/ucenter/follow-users', [UCenterController::class, 'followUsers']);

Route::get('/explore/index', [ExploreController::class, 'index']);

Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{news}', [NewsController::class, 'show']);

Route::get('/charities', [CharityController::class, 'index']);
Route::get('/charities/{charity}', [CharityController::class, 'show']);
Route::get('/charities/{charity}/events', [CharityController::class, 'activities']);


Route::get('/events', [ActivityController::class, 'index']);
Route::get('/events/{activity}', [ActivityController::class, 'show']);

Route::get('/users/{user}', [UserController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/ucenter/notifications', [UCenterController::class, 'notifications']);
    Route::put('/ucenter/information', [UCenterController::class, 'information']);
    Route::put('/ucenter/privacy', [UCenterController::class, 'privacy']);

    Route::post('/charities/{charity}/actions/follow', [CharityController::class, 'subscribe']);
    Route::delete('/charities/{charity}/actions/unfollow', [CharityController::class, 'unsubscribe']);

    Route::get('/events/{activity}/guests', [ActivityController::class, 'guests']);
    Route::put('/events/{activity}/actions/anonymous', [ActivityController::class, 'anonymous']);

    Route::get('/events/{activity}/ranks/donation-personal', [ActivityController::class, 'personRanks']);
    Route::get('/events/{activity}/ranks/donation-table', [ActivityController::class, 'tableRanks']);
    Route::get('/events/{activity}/ranks/donation-teams', [ActivityController::class, 'teamRanks']);

    Route::post('/events/{activity}/actions/follow', [ActivityController::class, 'subscribe']);
    Route::delete('/events/{activity}/actions/unfollow', [ActivityController::class, 'unsubscribe']);

    Route::post('/users/{user}/actions/follow', [UserController::class, 'follow']);
    Route::delete('/users/{user}/actions/unfollow', [UserController::class, 'unfollow']);
});















