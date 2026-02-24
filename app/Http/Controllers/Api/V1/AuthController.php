<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'ok' => false,
                'message' => 'Credenciais inválidas',
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        // Evita acúmulo de tokens antigos no app mobile
        $user->tokens()->delete();

        $token = $user->createToken('mobile')->plainTextToken;

        // API é stateless: não manter sessão web após gerar token
        Auth::logout();

        return response()->json([
            'ok' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();
        $bearer = $request->bearerToken();

        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        } elseif ($bearer) {
            PersonalAccessToken::findToken($bearer)?->delete();
        } elseif ($user) {
            // fallback para contexto stateful/transient
            $user->tokens()->delete();
        }

        return response()->json([
            'ok' => true,
            'message' => 'Logout realizado com sucesso.',
        ]);
    }
}
