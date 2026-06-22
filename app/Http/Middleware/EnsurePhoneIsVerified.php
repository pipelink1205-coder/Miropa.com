<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->hasVerifiedPhone()) {
            return $next($request);
        }

        if ($request->routeIs('phone.verify.*', 'logout', 'verification.*')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Debes verificar tu número de celular para continuar.',
            ], 403);
        }

        return redirect()->route('phone.verify.notice');
    }
}
