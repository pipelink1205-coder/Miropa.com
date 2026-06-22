<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $user = $action->execute($request->validated());

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'data' => UserResource::make($user->load('profile')),
            'token' => $token,
            'meta' => ['message' => 'Registro exitoso. Revisa tu correo para verificar tu cuenta.'],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! $user->password || ! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Tu cuenta ha sido suspendida o bloqueada.'],
            ]);
        }

        $user->update(['last_active_at' => now()]);
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'data' => UserResource::make($user->load('profile')),
            'token' => $token,
            'meta' => ['message' => 'Sesión iniciada.'],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'meta' => ['message' => 'Sesión cerrada correctamente.'],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => UserResource::make($request->user()->load('profile.location')),
        ]);
    }
}
