<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ActivationController extends Controller
{
    public function showForm(string $token)
    {
        $user = $this->findValidUserByToken($token);
        if (!$user) {
            return view('auth.activate', [
                'token' => $token,
                'invalid' => true,
                'message' => 'Link de ativacao invalido, expirado ou ja utilizado.',
            ]);
        }

        return view('auth.activate', [
            'token' => $token,
            'invalid' => false,
            'email' => $user->email,
        ]);
    }

    public function activate(Request $request, string $token)
    {
        $user = $this->findValidUserByToken($token);
        if (!$user) {
            return back()->with('error', 'Link de ativacao invalido, expirado ou ja utilizado.');
        }

        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $user->update([
            'password' => Hash::make($data['password']),
            'activated_at' => now(),
            'activation_token' => null,
            'activation_expires_at' => null,
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Conta ativada com sucesso. Faca login para acessar seu painel.');
    }

    private function findValidUserByToken(string $token): ?User
    {
        return User::query()
            ->where('activation_token', $token)
            ->whereNull('activated_at')
            ->where('activation_expires_at', '>', now())
            ->first();
    }
}
