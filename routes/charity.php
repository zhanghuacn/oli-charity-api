<?php

use App\Http\Controllers\Charity\ActivityController;
use App\Http\Controllers\Charity\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Charity Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware(['auth:api', 'scopes:place-charity', 'charity'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::apiResource('events', ActivityController::class);
});
