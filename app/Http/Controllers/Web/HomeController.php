<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;
use ReCaptcha\ReCaptcha;

class HomeController
{
    public function index(Request $request): JsonResponse|JsonResource
    {
        return Response::success(env('AWS_ACCESS_KEY_ID'));
    }

    public function reCaptcha()
    {
        return view('index');
    }

    public function store(Request $request, ReCaptcha $reCaptcha)
    {
        $request->validate([
            'g-recaptcha-response' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($reCaptcha) {
                    $response = $reCaptcha->verify($value, $_SERVER['REMOTE_ADDR']);
                    if ($response->isSuccess() === false) {
                        $fail($attribute . ' is invalid.');
                    }
                },
            ],
        ]);
        return view('welcome');
    }
}
