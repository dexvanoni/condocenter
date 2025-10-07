<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Authentication Routes
Route::get('login', function () {
    return view('auth.login');
})->middleware('guest')->name('login');

Route::post('login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended('dashboard');
    }

    return back()->withErrors([
        'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
    ])->onlyInput('email');
})->middleware('guest');

Route::post('logout', function (\Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->middleware('auth')->name('logout');

// Password Reset Routes
Route::get('forgot-password', function () {
    return view('auth.forgot-password');
})->middleware('guest')->name('password.request');

Route::post('forgot-password', function (\Illuminate\Http\Request $request) {
    $request->validate(['email' => 'required|email']);
    
    $status = \Illuminate\Support\Facades\Password::sendResetLink(
        $request->only('email')
    );

    return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
        ? back()->with(['status' => __($status)])
        : back()->withErrors(['email' => __($status)]);
})->middleware('guest')->name('password.email');

Route::get('reset-password/{token}', function (string $token) {
    return view('auth.reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::post('reset-password', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = \Illuminate\Support\Facades\Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => \Illuminate\Support\Facades\Hash::make($password)
            ])->save();
        }
    );

    return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
        ? redirect()->route('login')->with('status', __($status))
        : back()->withErrors(['email' => [__($status)]]);
})->middleware('guest')->name('password.update');

