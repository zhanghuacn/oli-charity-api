<?php

use App\Events\AuctionBidEvent;
use App\Http\Controllers\Api\V1\ActivityController;
use App\Http\Controllers\Api\V1\AlbumController;
use App\Http\Controllers\Api\V1\AuctionController;
use App\Http\Controllers\Api\V1\Auth\CaptchaController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\PaymentMethodController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\UcenterController;
use App\Http\Controllers\Api\V1\BazaarController;
use App\Http\Controllers\Api\V1\CharityController;
use App\Http\Controllers\Api\V1\GiftController;
use App\Http\Controllers\Api\V1\GoodsController;
use App\Http\Controllers\Api\V1\GroupController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\LotteryController;
use App\Http\Controllers\Api\V1\NewsController;
use App\Http\Controllers\Api\V1\SponsorController;
use App\Http\Controllers\Api\V1\TicketController;
use App\Http\Controllers\Api\V1\TransferController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\WebhookController;
use Illuminate\Http\Request;
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

Route::any('/stripe/webhook', [WebhookController::class, 'handleWebhook']);

Route::controller(HomeController::class)->group(function () {
    Route::get('/explore', 'explore');
    Route::get('/search', 'search');
});

Route::controller(NewsController::class)->group(function () {
    Route::get('/news', 'index');
    Route::get('/news/{news}', 'show');
});

Route::controller(RegisterController::class)->group(function () {
    Route::post('/auth/register-email', 'registerEmail');
    Route::post('/auth/register-phone', 'registerPhone');
});

Route::controller(LoginController::class)->group(function () {
    Route::post('/auth/login', 'login');
    Route::post('/auth/login-phone', 'loginByPhone');
    Route::post('/auth/login-email', 'loginByEmail');
    Route::post('/auth/login-social', 'socialite');
    Route::post('/auth/reset-password-email', 'resetByEmail');
    Route::post('/auth/reset-password-phone', 'resetByPhone');
    Route::post('/callbacks/sign_in_with_apple', 'callbackSignWithApple');
    Route::post('/callbacks/sign_in_with_oliview', 'callbackSignWithOliView');
});

Route::controller(CaptchaController::class)->group(function () {
    Route::post('/auth/captcha', 'captcha');
    Route::middleware(['throttle:5,1'])->group(function () {
        Route::post('/auth/phone-register-code', 'sendRegisterCodeByPhone');
        Route::post('/auth/email-register-code', 'sendRegisterCodeByEmail');
        Route::post('/auth/phone-login-code', 'sendLoginCodeByPhone');
        Route::post('/auth/email-login-code', 'sendLoginCodeByEmail');
    });
});

Route::controller(CharityController::class)->group(function () {
    Route::get('/charities', 'index');
    Route::get('/charities/{charity}', 'show');
    Route::get('/charities/{charity}/news', 'news');
    Route::get('/charities/{charity}/events', 'activities');
    Route::get('/charities/{charity}/historical-donation', 'chart');
    Route::get('/charities/{charity}/history', 'history');
    Route::get('/charities/{charity}/source', 'source');
});

Route::controller(SponsorController::class)->group(function () {
    Route::get('/sponsors', 'index');
    Route::get('/sponsors/{sponsor}', 'show');
    Route::get('/sponsors/{sponsor}/goods', 'goods');
});

Route::get('/events', [ActivityController::class, 'index']);
Route::get('/events/{activity}', [ActivityController::class, 'show']);
Route::get('/users/{user}', [UserController::class, 'show']);


Route::get('/events/{activity}/lotteries', [LotteryController::class, 'index']);
Route::get('/events/{activity}/lotteries/{lottery}', [LotteryController::class, 'show']);

Route::get('/events/{activity}/auctions', [AuctionController::class, 'index']);
Route::get('/auctions/{auction}', [AuctionController::class, 'show']);

Route::get('/events/{activity}/gifts', [GiftController::class, 'index']);
Route::get('/events/{activity}/gifts/{gift}', [GiftController::class, 'show']);

Route::get('/events/{activity}/goods', [GoodsController::class, 'index']);
Route::get('/events/{activity}/goods/{goods}', [GoodsController::class, 'show']);

Route::middleware(['auth:api', 'scopes:place-app'])->group(function () {
    Route::post('/auth/logout', [LoginController::class, 'logout']);

    Route::apiResource('/payment-method', PaymentMethodController::class);
    Route::any('/intent-payment-method', [PaymentMethodController::class, 'createSetupIntent']);

    Route::controller(UcenterController::class)->group(function () {
        Route::any('/payment-method/intent', [PaymentMethodController::class, 'createSetupIntent']);
        Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
        Route::post('/payment-method', [PaymentMethodController::class, 'store']);
        Route::put('/payment-method', [PaymentMethodController::class, 'update']);
        Route::get('/payment-method', [PaymentMethodController::class, 'show']);
        Route::delete('/payment-method', [PaymentMethodController::class, 'destroy']);
        Route::get('/default-payment-method', [PaymentMethodController::class, 'default']);
    });

    Route::controller(UcenterController::class)->group(function () {
        Route::get('/ucenter/notifications', 'notifications');
        Route::patch('/ucenter/notifications/read/{notification?}', 'read');
        Route::get('/ucenter/events', 'activities');
        Route::put('/ucenter/information', 'update');
        Route::put('/ucenter/privacy', 'privacy');
        Route::get('/ucenter/chart-history', 'chart');
        Route::get('/ucenter/charity-token', 'charityToken');
        Route::get('/ucenter/sponsor-token', 'sponsorToken');
        Route::get('/ucenter/follow-charities', 'followCharities');
        Route::get('/ucenter/follow-events', 'followActivities');
        Route::get('/ucenter/follow-users', 'followUsers');
        Route::put('/ucenter/bind-email', 'bindEmail');
        Route::put('/ucenter/bind-phone', 'bindPhone');
    });

    Route::controller(ActivityController::class)->group(function () {
        Route::post('/events/{activity}/actions/apply', 'apply');
        Route::get('/event/my-current', 'myCurrent');
        Route::get('/events/{activity}/ranks/donation-personal', 'personRanks');
        Route::get('/events/{activity}/ranks/donation-table', 'tableRanks');
        Route::get('/events/{activity}/ranks/donation-teams', 'teamRanks');
        Route::get('/events/{activity}/donation/my-history', 'history');
        Route::post('/events/{activity}/actions/donation', 'order');
        Route::post('/events/{activity}/actions/follow', 'favorite');
        Route::delete('/events/{activity}/actions/unfollow', 'unfavorite');
    });

    Route::controller(TicketController::class)->group(function () {
        Route::get('/events/{activity}/my-tickets', 'myTickets');
        Route::get('/events/{activity}/guests', 'guests');
        Route::post('/events/{activity}/actions/buy-tickets', 'buyTicket');
        Route::post('/events/{activity}/actions/free-collection', 'collection');
        Route::post('/events/{activity}/actions/scan', 'scan');
        Route::post('/events/{activity}/ticket-status', 'state');
        Route::put('/events/{activity}/actions/anonymous', 'anonymous');
    });

    Route::controller(LotteryController::class)->group(function () {
//        Route::get('/events/{activity}/lotteries', 'index');
//        Route::get('/events/{activity}/lotteries/{lottery}', 'show');
        Route::get('/events/{activity}/lotteries/{lottery}/qualification', 'qualification');
    });

    Route::controller(GoodsController::class)->group(function () {
//        Route::get('/events/{activity}/goods', 'index');
//        Route::get('/events/{activity}/goods/{goods}', 'show');
        Route::post('/events/{activity}/goods/{goods}/actions/order', 'order');
    });

    Route::controller(GiftController::class)->group(function () {
//        Route::get('/events/{activity}/gifts', 'index');
//        Route::get('/events/{activity}/gifts/{gift}', 'show');
        Route::post('/events/{activity}/gifts/{gift}/actions/like', 'like');
    });

    Route::controller(GroupController::class)->group(function () {
        Route::get('/events/{activity}/teams/search', 'search');
        Route::get('/events/{activity}/teams/details', 'show');
        Route::post('/events/{activity}/teams', 'store');
        Route::put('/events/{activity}/teams', 'update');
        Route::post('/events/{activity}/teams/actions/invite', 'invite');
        Route::post('/events/{activity}/teams/actions/accept', 'acceptInvite');
        Route::post('/events/{activity}/teams/actions/deny', 'denyInvite');
        Route::post('/events/{activity}/teams/actions/quit', 'quit');
    });

    Route::controller(TransferController::class)->group(function () {
        Route::get('/events/{activity}/transfers', 'index');
        Route::get('/events/{activity}/transfers', 'list');
        Route::post('/events/{activity}/actions/transfer', 'transfer');
        Route::post('/events/{activity}/actions/verify-transfer', 'check');
    });

    Route::controller(CharityController::class)->group(function () {
        Route::post('/charities/{charity}/actions/donation', 'order');
        Route::post('/charities/{charity}/actions/follow', 'favorite');
        Route::delete('/charities/{charity}/actions/unfollow', 'unfavorite');
    });

    Route::controller(UserController::class)->group(function () {
        Route::post('/users/{user}/actions/follow', 'follow');
        Route::delete('/users/{user}/actions/unfollow', 'unfollow');
        Route::get('/users/{user}/donation-history', 'history');
        Route::get('/users/{user}/charts/constitute', 'constitute');
        Route::get('/users/{user}/charts/history', 'chart');
    });

    Route::controller(AlbumController::class)->group(function () {
        Route::get('/events/{activity}/albums', 'index');
        Route::post('/events/{activity}/albums', 'store');
        Route::delete('/events/{activity}/albums/{album}', 'destroy');
    });

    Route::controller(BazaarController::class)->group(function () {
        Route::get('/bazaars', 'index');
        Route::get('/events/{activity}/warehouse', 'warehouse');
        Route::post('/bazaars/{bazaar}/affirm', 'affirm');
    });

    Route::controller(AuctionController::class)->group(function () {
//        Route::get('/events/{activity}/auctions', 'index');
//        Route::get('/auctions/{auction}', 'show');
        Route::get('/auction/orders', 'orders');
        Route::get('/events/{activity}/auction_orders', 'warehouse');
        Route::get('/auctions/{auction}/history', 'history');
        Route::post('/auctions/{auction}/bid', 'bid');
        Route::post('/auction/order/payment', 'payment');
        Route::post('/auctions/{auction}/affirm', 'affirm');
    });
});















