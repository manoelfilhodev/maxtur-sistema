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
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'ok' => false,
                'message' => 'Credenciais invalidas',
                'data' => null,
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();
        $user->tokens()->delete();
        $token = $user->createToken('mobile')->plainTextToken;
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
                ],
            ],
        ]);
    }

    /**
     * Logout do usuario
     *
     * Revoga o token atual.
     *
     * @group Auth
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
            ] : null,
        ]);
    }
}
