<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordChangeController extends Controller
{
    /**
     * Exibe formulário de troca de senha
     */
    public function show()
    {
        return view('auth.change-password');
    }

    /**
     * Processa a troca de senha
     */
    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'A senha atual é obrigatória.',
            'current_password.current_password' => 'A senha atual está incorreta.',
            'password.required' => 'A nova senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'password.confirmed' => 'A confirmação de senha não confere.',
        ]);

        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->password),
            'senha_temporaria' => false,
        ]);

        // Log da atividade
        $user->logActivity(
            'change_password',
            'authentication',
            'Alterou sua senha',
            []
        );

        return redirect()->route('dashboard')
            ->with('success', 'Senha alterada com sucesso!');
    }
}

