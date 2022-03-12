<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Mail\CaptchaShipped;
use Aws\Sns\SnsClient;
use Carbon\Carbon;
use Exception;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Jiannei\Response\Laravel\Support\Facades\Response;
use function now;

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

    public function sendLoginCodeByEmail(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'captcha_key' => 'required|string',
            'captcha_code' => 'required|string',
        ]);
        $captcha = Cache::get($request->get('captcha_key'));
        abort_if(!$captcha, 403, 'Graphic verification code is invalid');
        abort_if(!hash_equals($captcha['code'], $request->get('captcha_code')), 422, 'Graphic verification code error ');
        try {
            $code = str_pad(random_int(1, 999999), 6, 0, STR_PAD_LEFT);
            $email = $request->get('email');
            $key = 'email:login:code:' . $request->get('email');
            Mail::to($email)->send(new CaptchaShipped($code));
            Cache::put($key, $code, Carbon::now()->addMinutes(15));
        } catch (Exception $e) {
            abort(500, $e->getMessage());
        }
        return Response::success();
    }

    public function sendRegisterCodeByEmail(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'captcha_key' => 'required|string',
            'captcha_code' => 'required|string',
        ]);
        $captcha = Cache::get($request->get('captcha_key'));
        abort_if(!$captcha, 403, 'Graphic verification code is invalid');
        abort_if(!hash_equals($captcha['code'], $request->get('captcha_code')), 422, 'Graphic verification code error ');
        try {
            $code = str_pad(random_int(1, 999999), 6, 0, STR_PAD_LEFT);
            $email = $request->get('email');
            $key = 'email:register:code:' . $email;
            Mail::to($email)->send(new CaptchaShipped($code));
            Cache::put($key, $code, Carbon::now()->addMinutes(15));
        } catch (Exception $e) {
            abort(500, $e->getMessage());
        }
        return Response::success();
    }

    public function sendRegisterCodeByPhone(Request $request, SnsClient $snsClient): JsonResponse|JsonResource
    {
        $request->validate([
            'phone' => 'required|phone:AU,mobile|unique:users',
            'captcha_key' => 'required|string',
            'captcha_code' => 'required|string',
        ], [
            'phone.exists' => 'The phone number is not registered or disabled'
        ]);
        $captcha = Cache::get($request->get('captcha_key'));
        abort_if(!$captcha, 403, 'Graphic verification code is invalid');
        abort_if(!hash_equals($captcha['code'], $request->get('captcha_code')), 422, 'Graphic verification code error ');
        try {
            $code = str_pad(random_int(1, 999999), 6, 0, STR_PAD_LEFT);
            $phone = $request->get('phone');
            $key = 'phone:register:code:' . $phone;
            Cache::put($key, $code, Carbon::now()->addMinutes(15));
            $this->smsPublish($snsClient, $code, $phone);
            Cache::forget($request->get('captcha_key'));
        } catch (Exception $e) {
            Log::error($e->getMessage());
            abort(500, 'SMS sending failed');
        }
        return Response::success();
    }

    public function sendLoginCodeByPhone(Request $request, SnsClient $snsClient): JsonResponse|JsonResource
    {
        $request->validate([
            'phone' => 'required|phone:AU,mobile|exists:users',
            'captcha_key' => 'required|string',
            'captcha_code' => 'required|string',
        ], [
            'phone.exists' => 'The phone number is not registered or disabled'
        ]);
        $captcha = Cache::get($request->get('captcha_key'));
        abort_if(!$captcha, 403, 'Graphic verification code is invalid');
        abort_if(!hash_equals($captcha['code'], $request->get('captcha_code')), 422, 'Graphic verification code error ');
        try {
            $code = str_pad(random_int(1, 999999), 6, 0, STR_PAD_LEFT);
            $phone = $request->get('phone');
            $key = 'phone:login:code:' . $phone;
            Cache::put($key, $code, Carbon::now()->addMinutes(15));
            $this->smsPublish($snsClient, $code, $phone);
            Cache::forget($request->get('captcha_key'));
        } catch (Exception $e) {
            Log::error($e->getMessage());
            abort(500, 'SMS sending failed');
        }
        return Response::success();
    }

    /**
     * @param SnsClient $snsClient
     * @param string $code
     * @param mixed $phone
     * @return void
     */
    private function smsPublish(SnsClient $snsClient, string $code, mixed $phone): void
    {
        $snsClient->publish([
            'Message' => sprintf('【%s】The verification code is %s. Do not disclose the verification code to others. This verification code is valid for 15 minutes.', config('app.name'), $code),
            'PhoneNumber' => sprintf('+%s', $phone),
            'MessageAttributes' => [
                'AWS.SNS.SMS.SMSType' => [
                    'DataType' => 'String',
                    'StringValue' => 'Transactional',
                ]
            ],
        ]);
    }
}
