<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * Login do usuario
     *
     * Autentica e retorna token para uso via Bearer.
     *
     * @group Auth
     *
     * @bodyParam email string required Email do usuario. Example: dev@systex.com.br
     * @bodyParam password string required Senha do usuario. Example: 123456
     *
     * @response 200 {
     *  "ok": true,
     *  "message": "Login realizado com sucesso",
     *  "data": {
     *    "token": "1|abcdef...",
     *    "user": {"id": 1, "name": "Admin", "email": "admin@systex.com.br", "role": "admin", "operador_id": 1, "cliente_id": null}
     *  }
     * }
     * @response 401 {
     *  "ok": false,
     *  "message": "Credenciais invalidas",
     *  "data": null
     * }
     *
     * @unauthenticated
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->safe()->only(['email', 'password']);

        if (! Auth::attempt([...$credentials, 'ativo' => true])) {
            return response()->json([
                'ok' => false,
                'message' => 'Credenciais invalidas',
                'data' => null,
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();
        $expirationMinutes = (int) config('sanctum.expiration', 1440);
        $expiresAt = $expirationMinutes > 0 ? now()->addMinutes($expirationMinutes) : null;
        $deviceName = $request->validated('device_name') ?: 'mobile';
        $token = $user->createToken($deviceName, ['*'], $expiresAt)->plainTextToken;
        Auth::logout();

        return response()->json([
            'ok' => true,
            'message' => 'Login realizado com sucesso',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'operador_id' => $user->operador_id,
                    'cliente_id' => $user->cliente_id,
                    'tipo_recebimento' => $user->tipo_recebimento,
                    'valor_salario' => $user->valor_salario,
                    'valor_por_viagem' => $user->valor_por_viagem,
                ],
                'expires_at' => $expiresAt?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Logout do usuario
     *
     * Revoga o token atual.
     *
     * @group Auth
     *
     * @authenticated
     *
     * @response 200 {
     *  "ok": true,
     *  "message": "Logout realizado com sucesso",
     *  "data": null
     * }
     */
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
            $user->tokens()->delete();
        }

        return response()->json([
            'ok' => true,
            'message' => 'Logout realizado com sucesso',
            'data' => null,
        ]);
    }

    /**
     * Dados do usuario autenticado
     *
     * Retorna o perfil atual baseado no token Bearer.
     *
     * @group Me
     *
     * @authenticated
     *
     * @response 200 {
     *  "ok": true,
     *  "message": "Usuario autenticado",
     *  "data": {"id": 1, "name": "Admin", "email": "admin@systex.com.br", "role": "admin", "operador_id": 1, "cliente_id": null}
     * }
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'ok' => true,
            'message' => 'Usuario autenticado',
            'data' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'operador_id' => $user->operador_id,
                'cliente_id' => $user->cliente_id,
                'tipo_recebimento' => $user->tipo_recebimento,
                'valor_salario' => $user->valor_salario,
                'valor_por_viagem' => $user->valor_por_viagem,
            ] : null,
        ]);
    }

    public function refresh(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        if (! $user->ativo) {
            $user->currentAccessToken()?->delete();

            return response()->json(['ok' => false, 'message' => 'Usuário inativo.', 'data' => null], 403);
        }

        $currentToken = $user->currentAccessToken();
        $expirationMinutes = (int) config('sanctum.expiration', 1440);
        $expiresAt = $expirationMinutes > 0 ? now()->addMinutes($expirationMinutes) : null;
        $tokenName = $currentToken instanceof PersonalAccessToken ? $currentToken->name : 'mobile';
        $token = $user->createToken($tokenName, ['*'], $expiresAt)->plainTextToken;
        if ($currentToken instanceof PersonalAccessToken) {
            $currentToken->delete();
        }

        return response()->json([
            'ok' => true,
            'message' => 'Token renovado com sucesso',
            'data' => ['token' => $token, 'expires_at' => $expiresAt?->toIso8601String()],
        ]);
    }
}
