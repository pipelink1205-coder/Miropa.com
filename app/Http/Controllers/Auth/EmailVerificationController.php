<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
            'dev_mail_hint' => config('mail.default') === 'log',
        ]);
    }

    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'Inicia sesión con tu cuenta y pulsa "Reenviar correo".');
        }

        if (! $request->hasValidSignature()) {
            return redirect()->route('verification.notice')
                ->with('error', 'El enlace expiró. Pulsa "Reenviar correo" para generar uno nuevo.');
        }

        if ((string) $user->id !== (string) $id) {
            return redirect()->route('verification.notice')
                ->with('error', 'Este enlace pertenece a otra cuenta. Pulsa "Reenviar correo" estando logueada con tu usuario.');
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return redirect()->route('verification.notice')
                ->with('error', 'Enlace inválido. Pulsa "Reenviar correo".');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect()->route('phone.verify.notice')
            ->with('success', '¡Correo verificado! Ahora confirma tu número de celular.');
    }

    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('phone.verify.notice');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Correo de verificación enviado. Revisa storage/logs/laravel.log si usas MAIL_MAILER=log.');
    }
}
