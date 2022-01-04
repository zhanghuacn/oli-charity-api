<?php

use App\Http\Controllers\Sponsor\V1\ActivityController;
use App\Http\Controllers\Sponsor\V1\AuthController;
use App\Http\Controllers\Sponsor\V1\GoodsController;
use App\Http\Controllers\Sponsor\V1\PermissionController;
use App\Http\Controllers\Sponsor\V1\RoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sponsor Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/social-login', [AuthController::class, 'socialite']);
Route::post('/auth/register', [AuthController::class, 'register'])->name('sponsor.register');

Route::middleware(['auth:sponsor', 'scopes:place-sponsor', 'sponsor'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/events', [ActivityController::class, 'index']);
    Route::get('/events/{activity}', [ActivityController::class, 'show']);
    Route::put('/events/{activity}', [ActivityController::class, 'update']);

    Route::apiResources([
        'goods' => GoodsController::class,
        'roles' => RoleController::class,
        'permissions' => PermissionController::class,
    ], ['as' => 'sponsor']);
});
