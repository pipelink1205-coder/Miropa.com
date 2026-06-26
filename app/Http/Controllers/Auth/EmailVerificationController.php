<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyEmailCodeRequest;
use App\Services\EmailVerificationService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationController extends Controller
{
    public function notice(Request $request): Response|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $request->user()->hasVerifiedPhone()
                ? redirect()->route('dashboard')
                : redirect()->route('phone.verify.notice');
        }

        return Inertia::render('Auth/VerifyEmail', [
            'email' => $request->user()->email,
            'dev_mail_hint' => config('mail.default') === 'log',
        ]);
    }

    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'Inicia sesión con tu cuenta y solicita un código nuevo.');
        }

        if (! $request->hasValidSignature()) {
            return redirect()->route('verification.notice')
                ->with('error', 'El enlace expiró. Solicita un código nuevo.');
        }

        if ((string) $user->id !== (string) $id) {
            return redirect()->route('verification.notice')
                ->with('error', 'Este enlace pertenece a otra cuenta.');
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return redirect()->route('verification.notice')
                ->with('error', 'Enlace inválido. Solicita un código nuevo.');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect()->route('phone.verify.notice')
            ->with('success', '¡Correo verificado! Ahora confirma tu número de celular.');
    }

    public function send(Request $request, EmailVerificationService $service): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('phone.verify.notice');
        }

        $devCode = $service->sendCode($request->user());

        $redirect = back()->with('success', 'Te enviamos un código a tu correo.');

        if ($devCode) {
            $redirect->with('dev_code', $devCode);
        }

        return $redirect;
    }

    public function confirm(VerifyEmailCodeRequest $request, EmailVerificationService $service): RedirectResponse
    {
        $service->verifyCode($request->user(), $request->validated('code'));

        return redirect()->route('phone.verify.notice')
            ->with('success', '¡Correo verificado! Ahora confirma tu número de celular.');
    }
}
