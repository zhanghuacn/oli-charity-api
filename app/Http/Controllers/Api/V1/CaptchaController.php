<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Jiannei\Response\Laravel\Support\Facades\Response;
use ReCaptcha\ReCaptcha;

class CaptchaController extends Controller
{
    public function captcha(): JsonResponse|JsonResource
    {
        $key = 'captcha-' . Str::random(15);
        $captchaBuilder = new CaptchaBuilder(null, (new PhraseBuilder(4, '0123456789')));
        $captcha = $captchaBuilder->build();
        $expiredAt = now()->addMinutes(5);
        Cache::put($key, ['code' => $captcha->getPhrase()], $expiredAt);
        $result = [
            'captcha_key' => $key,
            'expired_at' => $expiredAt->toDateTimeString(),
            'captcha_image_content' => $captcha->inline(),
        ];
        return Response::success($result);
    }

    public function recaptcha(Request $request, ReCaptcha $captcha)
    {
        $request->validate([
            'g-recaptcha-response' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($captcha) {
                    $response = $captcha->verify($value, $_SERVER['REMOTE_ADDR']);
                    return $response->isSuccess();
                },
            ],
        ]);
        $g = $request->input('g-recaptcha-response');
        $resp = $captcha->setExpectedHostname('localhost')->verify($g, $_SERVER['REMOTE_ADDR']);
        if ($resp->isSuccess()) {
            echo "hi123";
        } else {
            var_dump($_SERVER['REMOTE_ADDR']);
        }
    }
}
