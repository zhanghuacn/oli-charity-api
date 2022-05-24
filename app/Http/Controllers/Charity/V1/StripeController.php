<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
use App\Models\Charity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use Jiannei\Response\Laravel\Support\Facades\Response;

class StripeController extends Controller
{
    public function board(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'return_url' => 'required|url',
            'refresh_url' => 'required|url',
        ]);
        return $this->handleBoardingRedirect($request->all());
    }

    public function return(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'return_url' => 'required|url',
            'refresh_url' => 'required|url',
        ]);
        return $this->handleBoardingRedirect($request->all());
    }

    public function refresh(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'return_url' => 'required|url',
            'refresh_url' => 'required|url',
        ]);
        return $this->handleBoardingRedirect($request->all());
    }

    private function handleBoardingRedirect(array $params): JsonResponse|JsonResource
    {
        $charity = Charity::findOrFail(getPermissionsTeamId());
        if ($charity->hasStripeAccountId() && $charity->hasCompletedOnboarding()) {
            return Response::success([
                'url' => 'https://dashboard.stripe.com/',
            ]);
        }
        $charity->deleteAndCreateStripeAccount();
        return Response::success([
            'url' => $charity->redirectToAccountOnboarding(
                URL::to($params['return_url']),
                URL::to($params['refresh_url'])
            )
        ]);
    }
}
