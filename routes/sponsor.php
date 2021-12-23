<?php

use App\Http\Controllers\Sponsor\V1\AuthController;
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

Route::middleware(['auth:sponsor', 'scopes:place-sponsor', 'sponsor'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});
