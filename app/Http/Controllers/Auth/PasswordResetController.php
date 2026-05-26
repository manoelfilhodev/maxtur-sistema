<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    public function requestForm()
    {
        return view('auth.forgot-password');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ], [
            'email.required' => 'Informe seu email.',
            'email.email' => 'Informe um email válido.',
            'password.required' => 'Informe a nova senha.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.letters' => 'A senha deve conter pelo menos uma letra.',
            'password.numbers' => 'A senha deve conter pelo menos um número.',
        ]);

        $user = User::query()
            ->where('email', $data['email'])
            ->first();

        if (! $user) {
            return back()
                ->withErrors(['email' => 'Não encontramos um usuário com esse email.'])
                ->onlyInput('email');
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
        ])->save();

        return redirect()
            ->route('login')
            ->with('success', 'Senha redefinida com sucesso. Entre usando sua nova senha.');
    }
}
