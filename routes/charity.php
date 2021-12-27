<?php

use App\Http\Controllers\Charity\V1\ActivityController;
use App\Http\Controllers\Charity\V1\AuthController;
use App\Http\Controllers\Charity\V1\NewsController;
use App\Http\Controllers\Charity\V1\PermissionController;
use App\Http\Controllers\Charity\V1\RoleController;
use App\Http\Controllers\Charity\V1\StripeController;
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

Route::middleware(['auth:charity', 'scopes:place-charity', 'charity'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::apiResources([
        'events' => ActivityController::class,
        'roles' => RoleController::class,
        'permissions' => PermissionController::class,
        'news' => NewsController::class,
    ], ['as' => 'charity']);

    Route::get('/stripe/board', [StripeController::class, 'board']);
    Route::get('/stripe/returning', [StripeController::class, 'returning']);
    Route::get('/stripe/refresh', [StripeController::class, 'refresh']);
});
