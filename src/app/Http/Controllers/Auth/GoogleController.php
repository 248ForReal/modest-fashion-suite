<?php

namespace App\Http\Controllers\Auth;

use App\Filament\Pages\WorkSettings;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectRegister()
    {
        if (!session('register_data')) {
            return redirect()->route('register')->with('error', 'Isi form pendaftaran dulu.');
        }
        return Socialite::driver('google')->redirect();
    }

    public function redirectLogin()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        // Kalau ada error dari Google (misal expired code, user cancel, dll)
        if (request()->has('error')) {
            session()->flash('swal_error', 'Login Google dibatalkan atau sesi kadaluarsa. Silakan coba lagi.');
            return redirect()->route('login');
        }

        // Kalau tidak ada code (misal user refresh halaman callback)
        if (!request()->has('code')) {
            return redirect()->route('login');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            session()->flash('swal_error', 'Sesi login Google kadaluarsa. Silakan login ulang.');
            return redirect()->route('login');
        }

        $registerData = session('register_data');
        $user = User::where('google_id', $googleUser->getId())->first();
        if ($user) {
            if ($user->isPending()) {
                session()->flash('swal_info', 'Akun kamu sedang menunggu persetujuan admin. Kamu akan dapat email notifikasi setelah disetujui.');
                return redirect()->route('login');
            }
            if ($user->status === 'inactive') {
                session()->flash('swal_error', 'Akun kamu telah dinonaktifkan. Hubungi admin.');
                return redirect()->route('login');
            }
            Auth::login($user);
            return $user->hasAnyRole(['super_admin', 'admin']) ? redirect('/admin') : redirect('/dashboard');
        }

        $user = User::where('email', $googleUser->getEmail())->first();
        if ($user) {
            $user->update(['google_id' => $googleUser->getId(), 'avatar' => $googleUser->getAvatar()]);
            if ($user->isPending()) {
                session()->flash('swal_info', 'Akun kamu sedang menunggu persetujuan admin. Kamu akan dapat email notifikasi setelah disetujui.');
                return redirect()->route('login');
            }
            if ($user->status === 'inactive') {
                session()->flash('swal_error', 'Akun kamu telah dinonaktifkan. Hubungi admin.');
                return redirect()->route('login');
            }
            // Cek apakah role hanya boleh login via admin panel
            if (!$user->hasAnyRole(['super_admin', 'admin'])) {
                $role = $user->roles->first()?->name;
                if ($role && !WorkSettings::canLoginAs($role, 'user')) {
                    session()->flash('swal_error', 'Akun kamu hanya bisa login melalui halaman Admin (/admin/login).');
                    return redirect()->route('login');
                }
            }
            Auth::login($user);
            return $user->hasAnyRole(['super_admin', 'admin']) ? redirect('/admin') : redirect('/dashboard');
        }

        // Kalau dari alur login (bukan register), email tidak ditemukan → error
        if (!$registerData) {
            session()->flash('swal_error', 'Akun dengan email ' . $googleUser->getEmail() . ' belum terdaftar. Silakan daftar terlebih dahulu.');
            return redirect()->route('login');
        }

        $user = User::create([
            'name'       => $registerData['name'] ?? $googleUser->getName(),
            'email'      => $googleUser->getEmail(),
            'phone'      => $registerData['phone'] ?? null,
            'birth_date' => $registerData['birth_date'] ?? null,
            'google_id'  => $googleUser->getId(),
            'avatar'     => $googleUser->getAvatar(),
            'status'     => 'pending',
        ]);

        session()->forget('register_data');
        return redirect()->route('pending')->with('just_registered', true);
    }
}
