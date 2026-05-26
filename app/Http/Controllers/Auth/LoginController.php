<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();
        if ($user && $user->requiresActivation() && !$user->activated_at) {
            return back()
                ->withErrors(['email' => 'Conta não ativada. Verifique o link de ativação.'])
                ->onlyInput('email');
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $authUser = $request->user();
            if ($authUser && strtolower((string) $authUser->role) === 'funcionario') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()
                    ->withErrors(['email' => 'Perfil funcionario deve acessar apenas o aplicativo.'])
                    ->onlyInput('email');
            }

            if ($authUser && ($authUser->isMaster() || $authUser->isAdmin() || $authUser->isOperador())) {
                return redirect()->intended('/painel');
            }

            return redirect()->intended('/app');
        }

        return back()
            ->withErrors(['email' => 'Credenciais inválidas.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
