<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! $user->password) {
            return back()->withErrors([
                'email' => 'Credenciales incorrectas o usa Google/Microsoft para entrar.',
            ]);
        }

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'Las credenciales son incorrectas.']);
        }

        if ($user->status !== 'active') {
            return back()->withErrors(['email' => 'Tu cuenta ha sido suspendida.']);
        }

        Auth::login($user, $request->boolean('remember'));
        $user->update(['last_active_at' => now()]);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
