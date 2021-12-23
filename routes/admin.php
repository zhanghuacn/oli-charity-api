<?php

use App\Http\Controllers\Admin\V1\ActivityController;
use App\Http\Controllers\Admin\V1\AdminController;
use App\Http\Controllers\Admin\V1\AuthController;
use App\Http\Controllers\Admin\V1\CharityController;
use App\Http\Controllers\Admin\V1\NewsController;
use App\Http\Controllers\Admin\V1\PermissionController;
use App\Http\Controllers\Admin\V1\RoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware(['auth:admin', 'scopes:place-admin', 'admin'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::apiResources([
        'admins' => AdminController::class,
        'roles' => RoleController::class,
        'permissions' => PermissionController::class,
        'news' => NewsController::class,
    ], ['as' => 'admin']);

    Route::get('/charities', [CharityController::class, 'index']);
    Route::get('/charities/{charity}', [CharityController::class, 'show']);
    Route::put('/charities/{charity}/audit', [CharityController::class, 'audit']);

    Route::get('/activities', [ActivityController::class, 'index']);
    Route::get('/activities/{activity}', [ActivityController::class, 'show']);
    Route::put('/activities/{activity}/audit', [ActivityController::class, 'audit']);
});
