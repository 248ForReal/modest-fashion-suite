<?php

namespace App\Http\Middleware;

use App\Filament\Pages\WorkSettings;
use Closure;
use Illuminate\Http\Request;

class EnsureUserModuleAccess
{
    public function handle(Request $request, Closure $next, string $module): mixed
    {
        $user = auth()->user();
        if (!$user) return redirect()->route('login');

        // Admin selalu boleh
        if ($user->hasAnyRole(['super_admin', 'admin'])) return $next($request);

        $role = $user->roles->first()?->name;
        if (!$role) abort(403);

        // Cek modul aktif dan role diizinkan
        if (!WorkSettings::isUserModuleEnabled($module)) {
            abort(403, 'Modul ini sedang nonaktif.');
        }

        if (!WorkSettings::canAccessUserModule($role, $module)) {
            abort(403, 'Role kamu tidak memiliki akses ke modul ini.');
        }

        return $next($request);
    }
}
