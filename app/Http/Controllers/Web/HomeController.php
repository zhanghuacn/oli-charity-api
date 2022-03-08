<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;

class HomeController
{
    public function index(Request $request): JsonResponse|JsonResource
    {
        return Response::success($request->header('x-vapor-source-ip'));
    }
}
