<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CharityController;
use App\Http\Controllers\Api\GoodsController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\LotteryController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\UcenterController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WebhookController;
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

Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook']);

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/social-login', [AuthController::class, 'socialiteLogin']);
Route::post('/auth/social-bind', [AuthController::class, 'socialiteBind']);
Route::post('/auth/social-register', [AuthController::class, 'socialiteRegister']);

Route::get('/ucenter/follow-charities', [UcenterController::class, 'followCharities']);
Route::get('/ucenter/follow-events', [UcenterController::class, 'followActivities']);
Route::get('/ucenter/follow-users', [UcenterController::class, 'followUsers']);

Route::get('/explore', [HomeController::class, 'explore']);
Route::get('/search', [HomeController::class, 'search']);

Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{news}', [NewsController::class, 'show']);

Route::get('/charities', [CharityController::class, 'index']);
Route::get('/charities/{charity}', [CharityController::class, 'show']);
Route::get('/charities/{charity}/events', [CharityController::class, 'activities']);


Route::get('/events', [ActivityController::class, 'index']);
Route::get('/events/{activity}', [ActivityController::class, 'show']);

Route::get('/users/{user}', [UserController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/ucenter/notifications', [UcenterController::class, 'notifications']);
    Route::get('/ucenter/events', [UcenterController::class, 'activities']);
    Route::get('/ucenter/information', [UcenterController::class, 'show']);
    Route::put('/ucenter/information', [UcenterController::class, 'update']);
    Route::put('/ucenter/privacy', [UcenterController::class, 'privacy']);
    Route::get('/ucenter/chart-history', [UcenterController::class, 'chart']);

    Route::post('/events/{activity}/actions/apply', [TicketController::class, 'apply']);
    Route::post('/events/{activity}/actions/buy-tickets', [TicketController::class, 'buyTicket']);
    Route::post('/events/{activity}/actions/scan', [TicketController::class, 'scan']);
    Route::get('/events/{activity}/my-tickets', [TicketController::class, 'myTickets']);
    Route::get('/events/{activity}/guests', [TicketController::class, 'guests']);
    Route::put('/events/{activity}/actions/anonymous', [TicketController::class, 'anonymous']);

    Route::get('/events/{activity}/lotteries', [LotteryController::class, 'index']);
    Route::get('/events/{activity}/lotteries/{lottery}', [LotteryController::class, 'show']);

    Route::get('/events/{activity}/goods', [GoodsController::class, 'index']);
    Route::get('/events/{activity}/goods/{goods}', [GoodsController::class, 'show']);
    Route::post('/events/{activity}/goods/{goods}/actions/order', [GoodsController::class, 'order']);

    Route::get('/event/my-current', [ActivityController::class, 'myCurrent']);
    Route::get('/events/{activity}/ranks/donation-personal', [ActivityController::class, 'personRanks']);
    Route::get('/events/{activity}/ranks/donation-table', [ActivityController::class, 'tableRanks']);
    Route::get('/events/{activity}/ranks/donation-teams', [ActivityController::class, 'teamRanks']);
    Route::get('/events/{activity}/donation/my-history', [ActivityController::class, 'history']);

    Route::get('/events/{activity}/teams/search', [TeamController::class, 'search']);
    Route::get('/events/{activity}/teams/details', [TeamController::class, 'show']);
    Route::post('/events/{activity}/teams', [TeamController::class, 'store']);
    Route::put('/events/{activity}/teams', [TeamController::class, 'update']);
    Route::post('/events/{activity}/teams/actions/invite', [TeamController::class, 'invite']);
    Route::post('/events/{activity}/teams/actions/accept', [TeamController::class, 'acceptInvite']);
    Route::post('/events/{activity}/teams/actions/deny', [TeamController::class, 'denyInvite']);
    Route::post('/events/{activity}/teams/actions/quit', [TeamController::class, 'quit']);

    Route::get('/events/{activity}/transfers', [TransferController::class, 'index']);
    Route::post('/events/{activity}/actions/donation', [ActivityController::class, 'order']);
    Route::post('/events/{activity}/actions/transfer', [TransferController::class, 'transfer']);
    Route::post('/events/{activity}/actions/verify-transfer', [TransferController::class, 'check']);

    Route::post('/events/{activity}/actions/follow', [ActivityController::class, 'favorite']);
    Route::delete('/events/{activity}/actions/unfollow', [ActivityController::class, 'unfavorite']);

    Route::post('/charities/{charity}/actions/donation', [CharityController::class, 'order']);
    Route::post('/charities/{charity}/actions/follow', [CharityController::class, 'favorite']);
    Route::delete('/charities/{charity}/actions/unfollow', [CharityController::class, 'unfavorite']);

    Route::post('/users/{user}/actions/follow', [UserController::class, 'follow']);
    Route::delete('/users/{user}/actions/unfollow', [UserController::class, 'unfollow']);
    Route::get('/users/{user}/donation-history', [UserController::class, 'history']);
    Route::get('/users/{user}/charts/constitute', [UserController::class, 'constitute']);
    Route::get('/users/{user}/charts/history', [UserController::class, 'chart']);
});















