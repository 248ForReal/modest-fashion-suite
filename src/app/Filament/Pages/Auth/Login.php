<?php

namespace App\Filament\Pages\Auth;

use App\Models\Setting;
use App\Models\User;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Checkbox;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;

class Login extends BaseLogin
{
    protected function getRememberFormComponent(): Component
    {
        return Checkbox::make('remember')->hidden();
    }

    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();
        $user = User::where('email', $data['email'])->first();

        if ($user && !$user->hasAnyRole(['super_admin', 'admin'])) {
            $role = $user->roles->first()?->name;
            if ($role) {
                $loginAccess = Setting::get("role_login_access.{$role}", 'user');
                if ($loginAccess === 'user') {
                    Notification::make()
                        ->title('Akses Ditolak')
                        ->body('Akun kamu hanya bisa login melalui halaman User. Silakan login di /login.')
                        ->danger()
                        ->persistent()
                        ->send();
                    return null;
                }
            }
        }

        return parent::authenticate();
    }
}
