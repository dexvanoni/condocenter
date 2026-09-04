<?php

use App\Http\Controllers\SelfRegistrationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Authentication Routes
Route::get('login', function (\Illuminate\Http\Request $request) {
    if ($request->filled('redirect')) {
        $redirect = $request->query('redirect');
        $target = null;

        if (filter_var($redirect, FILTER_VALIDATE_URL)) {
            $redirectHost = parse_url($redirect, PHP_URL_HOST);
            if ($redirectHost && $redirectHost === $request->getHost()) {
                $target = $redirect;
            }
        } elseif (is_string($redirect) && str_starts_with($redirect, '/')) {
            $target = url($redirect);
        }

        if ($target) {
            session(['url.intended' => $target]);
        }
    }

    return view('auth.login');
})->middleware('guest')->name('login');

Route::post('login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && !$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Seu cadastro ainda aguarda aprovação da administração. Você receberá acesso assim que for aprovado.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();
        return redirect()->intended('dashboard');
    }

    return back()->withErrors([
        'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
    ])->onlyInput('email');
})->middleware(['guest', 'throttle:auth-login']);

Route::get('register', [SelfRegistrationController::class, 'create'])
    ->middleware('guest')
    ->name('register');

Route::post('register', [SelfRegistrationController::class, 'store'])
    ->middleware('guest')
    ->name('register.store');

Route::get('register/success', [SelfRegistrationController::class, 'success'])
    ->middleware('guest')
    ->name('register.success');

Route::post('register/lookup', [SelfRegistrationController::class, 'lookupCode'])
    ->middleware('guest')
    ->name('register.lookup');

Route::get('register/units', [SelfRegistrationController::class, 'units'])
    ->middleware('guest')
    ->name('register.units');

Route::get('register/moradores', [SelfRegistrationController::class, 'moradores'])
    ->middleware('guest')
    ->name('register.moradores');

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
})->middleware(['guest', 'throttle:auth-password-reset'])->name('password.email');

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

