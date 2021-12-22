<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
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
    ]);
});
