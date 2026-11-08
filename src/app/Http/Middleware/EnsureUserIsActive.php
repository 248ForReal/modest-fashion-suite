<?php

namespace App\Http\Middleware;

use App\Filament\Pages\WorkSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user->isPending()) {
            return redirect()->route('pending');
        }

        if ($user->status === 'inactive') {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Akun kamu dinonaktifkan.');
        }

        // Cek apakah role ini hanya boleh login via admin panel
        if (!$user->hasAnyRole(['super_admin', 'admin'])) {
            $role = $user->roles->first()?->name;
            if ($role && !WorkSettings::canLoginAs($role, 'user')) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')
                    ->with('swal_error', 'Akun kamu hanya bisa login melalui halaman Admin (/admin/login).');
            }
        }

        return $next($request);
    }
}
