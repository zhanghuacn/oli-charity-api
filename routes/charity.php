<?php

use App\Http\Controllers\Charity\V1\ActivityController;
use App\Http\Controllers\Charity\V1\ApplyController;
use App\Http\Controllers\Charity\V1\AuthController;
use App\Http\Controllers\Charity\V1\CharityController;
use App\Http\Controllers\Charity\V1\GiftController;
use App\Http\Controllers\Charity\V1\HistoryController;
use App\Http\Controllers\Charity\V1\HomeController;
use App\Http\Controllers\Charity\V1\LotteryController;
use App\Http\Controllers\Charity\V1\NewsController;
use App\Http\Controllers\Charity\V1\NotifyController;
use App\Http\Controllers\Charity\V1\PermissionController;
use App\Http\Controllers\Charity\V1\RoleController;
use App\Http\Controllers\Charity\V1\SponsorController;
use App\Http\Controllers\Charity\V1\StaffController;
use App\Http\Controllers\Charity\V1\StripeController;
use App\Http\Controllers\Charity\V1\TicketController;
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

Route::get('/auth/login/{provider}', [AuthController::class, 'redirectToProvider']);
Route::get('/auth/login/{provider}/callback', [AuthController::class, 'handleProviderCallback']);

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/social-login', [AuthController::class, 'socialite']);
Route::post('/auth/register', [AuthController::class, 'register'])->name('charity.register');

Route::middleware(['auth:charity', 'scopes:place-charity', 'charity'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/home/search', [HomeController::class, 'search']);
    Route::get('/home/dashboard', [HomeController::class, 'dashboard']);

    Route::apiResources([
        'roles' => RoleController::class,
        'permissions' => PermissionController::class,
        'news' => NewsController::class,
    ], ['as' => 'charity']);

    Route::get('/events', [ActivityController::class, 'index']);
    Route::get('/events/{activity}', [ActivityController::class, 'show']);
    Route::get('/events/{activity}/audit-details', [ActivityController::class, 'details']);
    Route::get('/events/{activity}/details', [ActivityController::class, 'views']);
    Route::post('/events', [ActivityController::class, 'store']);
    Route::post('/events/{activity}/submit', [ActivityController::class, 'submit']);
    Route::put('/events/{activity}', [ActivityController::class, 'update']);
    Route::delete('/events/{activity}', [ActivityController::class, 'destroy']);
    Route::post('/events/{activity}/bulk-sms', [NotifyController::class, 'send']);

    Route::get('/events/{activity}/applies', [ApplyController::class, 'index']);
    Route::post('/events/{activity}/applies/{apply}/audit', [ApplyController::class, 'audit']);

    Route::get('/events/{activity}/gifts', [GiftController::class, 'index']);
    Route::get('/events/{activity}/gifts/{gift}/users', [GiftController::class, 'users']);

    Route::get('/events/{activity}/tickets', [ActivityController::class, 'tickets']);

    Route::get('/events/{activity}/albums', [ActivityController::class, 'albumsIndex']);
    Route::post('/events/{activity}/albums/upload', [ActivityController::class, 'albumStore']);
    Route::delete('/events/{activity}/albums/{album}', [ActivityController::class, 'albumsDelete']);

    Route::get('/events/{activity}/seat-config', [ActivityController::class, 'seatConfig']);
    Route::post('/events/{activity}/seat-allocation', [ActivityController::class, 'seatAllocation']);
    Route::post('/lotteries/{lottery}/draw', [LotteryController::class, 'draw']);

    Route::get('/events/{activity}/tickets/export', [TicketController::class, 'export']);
    Route::post('/events/{activity}/tickets/import', [TicketController::class, 'import']);

    Route::get('/sponsors', [SponsorController::class, 'index']);

    Route::get('/charity', [CharityController::class, 'show']);
    Route::put('/charity', [CharityController::class, 'update']);

    Route::get('/staffs', [StaffController::class, 'index']);
    Route::post('/staffs', [StaffController::class, 'store']);
    Route::delete('/staffs/{user}', [StaffController::class, 'destroy']);

    Route::get('/charity', [CharityController::class, 'show']);
    Route::put('/charity', [CharityController::class, 'update']);

    Route::post('/stripe/board', [StripeController::class, 'board']);
    Route::post('/stripe/return', [StripeController::class, 'return']);
    Route::post('/stripe/refresh', [StripeController::class, 'refresh']);

    Route::get('/history', [HistoryController::class, 'index']);
});
