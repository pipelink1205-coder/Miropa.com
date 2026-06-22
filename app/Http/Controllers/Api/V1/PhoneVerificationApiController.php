<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendPhoneVerificationRequest;
use App\Http\Requests\Auth\VerifyPhoneCodeRequest;
use App\Http\Resources\UserResource;
use App\Services\PhoneVerificationService;
use Illuminate\Http\JsonResponse;

class PhoneVerificationApiController extends Controller
{
    public function send(SendPhoneVerificationRequest $request, PhoneVerificationService $service): JsonResponse
    {
        $devCode = $service->sendCode($request->user(), $request->validated('phone'));

        return response()->json([
            'meta' => [
                'message' => 'Código enviado por SMS.',
                'dev_code' => $devCode,
            ],
        ]);
    }

    public function verify(VerifyPhoneCodeRequest $request, PhoneVerificationService $service): JsonResponse
    {
        $service->verifyCode($request->user(), $request->validated('code'));

        return response()->json([
            'data' => UserResource::make($request->user()->fresh()->load('profile')),
            'meta' => ['message' => 'Teléfono verificado correctamente.'],
        ]);
    }
}
