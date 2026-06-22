<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendPhoneVerificationRequest;
use App\Http\Requests\Auth\VerifyPhoneCodeRequest;
use App\Services\PhoneVerificationService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PhoneVerificationController extends Controller
{
    public function show(): Response
    {
        $user = auth()->user();

        return Inertia::render('Auth/VerifyPhone', [
            'phone' => $user->phone,
            'phone_verified' => $user->hasVerifiedPhone(),
        ]);
    }

    public function send(SendPhoneVerificationRequest $request, PhoneVerificationService $service): RedirectResponse
    {
        $devCode = $service->sendCode($request->user(), $request->validated('phone'));

        $redirect = back()->with('success', 'Te enviamos un código por SMS.');

        if ($devCode) {
            $redirect->with('dev_code', $devCode);
        }

        return $redirect;
    }

    public function verify(VerifyPhoneCodeRequest $request, PhoneVerificationService $service): RedirectResponse
    {
        $service->verifyCode($request->user(), $request->validated('code'));

        return redirect()
            ->intended(route('dashboard'))
            ->with('success', '¡Número verificado! Ya puedes usar Mi Ropa.');
    }
}
