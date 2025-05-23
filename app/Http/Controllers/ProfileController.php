<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        // Atualiza os dados do usuário logado
        $request->user()->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Retorna com alerta SweetAlert de sucesso
        return redirect()->route('profile.show')->with('success', 'Dados atualizados com sucesso!');
    }


    /**
     * Delete the user's account.
     */
    public function destroy(Request $request)
    {
        $user = $request->user();

        // Executa a exclusão do usuário
        $user->delete();

        return redirect()->route('login')->with('success', 'Sua conta foi excluída com sucesso!');
    }
}
