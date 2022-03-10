<?php

use App\Http\Controllers\Sponsor\V1\ActivityController;
use App\Http\Controllers\Sponsor\V1\AuthController;
use App\Http\Controllers\Sponsor\V1\GiftController;
use App\Http\Controllers\Sponsor\V1\GoodsController;
use App\Http\Controllers\Sponsor\V1\HomeController;
use App\Http\Controllers\Sponsor\V1\PermissionController;
use App\Http\Controllers\Sponsor\V1\RoleController;
use App\Http\Controllers\Sponsor\V1\SponsorController;
use App\Http\Controllers\Sponsor\V1\StaffController;
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

    Route::get('/home/search', [HomeController::class, 'search']);
    Route::get('/home/dashboard', [HomeController::class, 'dashboard']);

    Route::get('/events', [ActivityController::class, 'index']);
    Route::get('/events/{activity}', [ActivityController::class, 'show']);
    Route::put('/events/{activity}', [ActivityController::class, 'update']);

    Route::get('/events/{activity}/gifts', [GiftController::class, 'index']);
    Route::get('/events/{activity}/gifts/{gift}/users', [GiftController::class, 'users']);

    Route::get('/sponsor', [SponsorController::class, 'show']);
    Route::put('/sponsor', [SponsorController::class, 'update']);

    Route::get('/staffs', [StaffController::class, 'index']);
    Route::post('/staffs', [StaffController::class, 'store']);
    Route::delete('/staffs/{user}', [StaffController::class, 'destroy']);

    Route::apiResources([
        'goods' => GoodsController::class,
        'roles' => RoleController::class,
        'permissions' => PermissionController::class,
    ], ['as' => 'sponsor']);
});
