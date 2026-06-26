<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\EmailVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(RegisterRequest $request, RegisterUserAction $action, EmailVerificationService $emailVerification): RedirectResponse
    {
        $user = $action->execute($request->validated());

        Auth::login($user);
        $request->session()->regenerate();

        $devCode = $emailVerification->sendCode($user);

        $redirect = redirect()->route('verification.notice')
            ->with('success', 'Te enviamos un código a tu correo.');

        if ($devCode) {
            $redirect->with('dev_code', $devCode);
        }

        return $redirect;
    }
}
