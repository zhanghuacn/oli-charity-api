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
        return Response::success();
    }

//    public function test()
//    {
//        return view('index');
//    }
//
//    public function test2(Request $request, ReCaptcha $reCaptcha)
//    {
//        $request->validate([
//            'g-recaptcha-response' => [
//                'required',
//                'numeric',
//                function ($attribute, $value, $fail) use ($reCaptcha) {
//                    $response = $reCaptcha->verify($value, $_SERVER['REMOTE_ADDR']);
//                    return $response->isSuccess();
//                },
//            ],
//        ]);
//        echo 123;
//    }
}
