<?php

use App\Http\Controllers\Api\V1\ActivityController;
use App\Http\Controllers\Api\V1\AlbumController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BazaarController;
use App\Http\Controllers\Api\V1\CharityController;
use App\Http\Controllers\Api\V1\GoodsController;
use App\Http\Controllers\Api\V1\GroupController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\LotteryController;
use App\Http\Controllers\Api\V1\NewsController;
use App\Http\Controllers\Api\V1\SponsorController;
use App\Http\Controllers\Api\V1\TicketController;
use App\Http\Controllers\Api\V1\TransferController;
use App\Http\Controllers\Api\V1\UcenterController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\WebhookController;
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

Route::get('/email/verify/{id}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/email/verify/resend', [AuthController::class, 'verifyEmail'])
    ->middleware(['auth:api', 'throttle:6,1'])
    ->name('verification.send');

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/social-login', [AuthController::class, 'socialite']);
Route::post('/auth/social-bind', [AuthController::class, 'socialiteBind']);
Route::post('/auth/social-register', [AuthController::class, 'socialiteRegister']);
Route::post('/auth/send-register-code', [AuthController::class, 'sendRegisterCodeEmail'])->middleware('throttle:1,10');
Route::post('/auth/send-forgot-code', [AuthController::class, 'sendForgotCodeEmail'])->middleware('throttle:1,10');
Route::post('/auth/reset-password', [AuthController::class, 'reset'])->name('password.reset');

Route::post('/callbacks/sign_in_with_apple', [AuthController::class, 'callbackSignWithApple']);
Route::post('/callbacks/sign_in_with_oliview', [AuthController::class, 'callbackSignWithOliView']);

Route::get('/explore', [HomeController::class, 'explore']);
Route::get('/search', [HomeController::class, 'search']);

Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{news}', [NewsController::class, 'show']);

Route::get('/charities', [CharityController::class, 'index']);
Route::get('/charities/{charity}', [CharityController::class, 'show']);
Route::get('/charities/{charity}/news', [CharityController::class, 'news']);
Route::get('/charities/{charity}/events', [CharityController::class, 'activities']);
Route::get('/charities/{charity}/historical-donation', [CharityController::class, 'chart']);
Route::get('/charities/{charity}/history', [CharityController::class, 'history']);
Route::get('/charities/{charity}/source', [CharityController::class, 'source']);

Route::get('/sponsors', [SponsorController::class, 'index']);
Route::get('/sponsors/{sponsor}', [SponsorController::class, 'show']);
Route::get('/sponsors/{sponsor}/goods', [SponsorController::class, 'goods']);


Route::get('/events', [ActivityController::class, 'index']);
Route::get('/events/{activity}', [ActivityController::class, 'show']);

Route::get('/users/{user}', [UserController::class, 'show']);

Route::middleware(['auth:api', 'scopes:place-app'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/ucenter/notifications', [UcenterController::class, 'notifications']);
    Route::get('/ucenter/events', [UcenterController::class, 'activities']);
    Route::put('/ucenter/information', [UcenterController::class, 'update']);
    Route::put('/ucenter/privacy', [UcenterController::class, 'privacy']);
    Route::get('/ucenter/chart-history', [UcenterController::class, 'chart']);
    Route::get('/ucenter/charity-token', [UcenterController::class, 'charityToken']);
    Route::get('/ucenter/sponsor-token', [UcenterController::class, 'sponsorToken']);

    Route::get('/ucenter/follow-charities', [UcenterController::class, 'followCharities']);
    Route::get('/ucenter/follow-events', [UcenterController::class, 'followActivities']);
    Route::get('/ucenter/follow-users', [UcenterController::class, 'followUsers']);

    Route::post('/events/{activity}/actions/apply', [ActivityController::class, 'apply']);
    Route::post('/events/{activity}/actions/buy-tickets', [TicketController::class, 'buyTicket']);
    Route::post('/events/{activity}/actions/free-collection', [TicketController::class, 'collection']);
    Route::post('/events/{activity}/actions/scan', [TicketController::class, 'scan']);
    Route::get('/events/{activity}/my-tickets', [TicketController::class, 'myTickets']);
    Route::post('/events/{activity}/ticket-status', [TicketController::class, 'state']);
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

    Route::get('/events/{activity}/teams/search', [GroupController::class, 'search']);
    Route::get('/events/{activity}/teams/details', [GroupController::class, 'show']);
    Route::post('/events/{activity}/teams', [GroupController::class, 'store']);
    Route::put('/events/{activity}/teams', [GroupController::class, 'update']);
    Route::post('/events/{activity}/teams/actions/invite', [GroupController::class, 'invite']);
    Route::post('/events/{activity}/teams/actions/accept', [GroupController::class, 'acceptInvite']);
    Route::post('/events/{activity}/teams/actions/deny', [GroupController::class, 'denyInvite']);
    Route::post('/events/{activity}/teams/actions/quit', [GroupController::class, 'quit']);

    Route::get('/events/{activity}/transfers', [TransferController::class, 'index']);
    Route::get('/events/{activity}/transfers', [TransferController::class, 'list']);
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

    Route::get('/events/{activity}/albums', [AlbumController::class, 'index']);
    Route::post('/events/{activity}/albums', [AlbumController::class, 'store']);
    Route::delete('/events/{activity}/albums/{album}', [AlbumController::class, 'destroy']);

    Route::get('/bazaars', [BazaarController::class, 'index']);
    Route::get('/events/{activity}/warehouse', [BazaarController::class, 'warehouse']);
    Route::post('/bazaars/{bazaar}/affirm', [BazaarController::class, 'affirm']);
});















