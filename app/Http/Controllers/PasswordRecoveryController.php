<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class PasswordRecoveryController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'string', 'email', 'max:255']]);
        Password::sendResetLink($credentials);

        return back()->with('status', 'Si el correo está registrado, recibirás un enlace para recuperar tu acceso.');
    }

    public function edit(Request $request, string $token): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email', '')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', PasswordRule::min(12)->letters()->numbers()],
        ]);

        $status = Password::reset($data, function ($user, string $password) {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => 'El enlace no es válido o venció. Solicita uno nuevo.'])->onlyInput('email');
        }

        return redirect()->route('login')->with('status', 'Tu contraseña se actualizó. Inicia sesión con tu nueva contraseña.');
    }
}
