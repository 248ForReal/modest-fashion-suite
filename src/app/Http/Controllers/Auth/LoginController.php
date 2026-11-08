<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser && $existingUser->isPending()) {
            return back()->with('swal_info', 'Akun kamu sedang menunggu persetujuan admin. Kamu akan dapat email notifikasi setelah disetujui.')->onlyInput('email');
        }

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->with('swal_error', 'Email atau password salah.')->onlyInput('email');
        }

        $user = Auth::user();

        if ($user->isPending()) {
            Auth::logout();
            return redirect()->route('pending');
        }

        if ($user->status === 'inactive') {
            Auth::logout();
            return back()->with('swal_error', 'Akun kamu telah dinonaktifkan. Hubungi admin.');
        }

        // Cek apakah role boleh login di halaman user (/login)
        if (!$user->hasAnyRole(['super_admin', 'admin'])) {
            $role = $user->roles->first()?->name;
            if ($role) {
                $loginAccess = Setting::get("role_login_access.{$role}", 'user');
                if ($loginAccess === 'admin') {
                    Auth::logout();
                    return back()->with('swal_error', 'Akun kamu hanya bisa login melalui halaman Admin. Silakan login di /admin/login.')->onlyInput('email');
                }
            }
        }

        $request->session()->regenerate();

        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return redirect()->intended('/admin');
        }

        return redirect('/dashboard');
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
