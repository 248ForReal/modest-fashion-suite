<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordController extends Controller
{
    public function showForgot()
    {
        return view('auth.forgot-password');
    }

    public function sendReset(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));
        return $status === Password::ResetLinkSent
            ? back()->with('status', 'Link reset password sudah dikirim ke email kamu.')
            : back()->withErrors(['email' => __($status)]);
    }

    public function showReset(string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            fn($user, $password) => $user->forceFill(['password' => $password])->save()
        );

        return $status === Password::PasswordReset
            ? redirect()->route('login')->with('swal_success', 'Password berhasil dibuat! Silakan masuk.')
            : back()->with('swal_error', 'Link sudah kedaluwarsa atau tidak valid. Hubungi admin.');
    }
}
