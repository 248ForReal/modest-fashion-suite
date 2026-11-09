<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'phone', 'birth_date', 'google_id', 'avatar', 'status', 'employment_type', 'bank_name', 'bank_account', 'default_allowance'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date'        => 'date',
            'password'          => 'hashed',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (!$this->isActive()) return false;
        if ($this->hasAnyRole(['super_admin', 'admin'])) return true;

        $role = $this->roles->first()?->name;
        if (!$role) return false;

        $loginAccess = \App\Models\Setting::get("role_login_access.{$role}", 'user');
        if (!in_array($loginAccess, ['admin', 'both'])) return false;

        return $this->hasAnyRole([
            'designer', 'keuangan', 'marketing', 'team_leader', 'tailor',
            'support', 'magang', 'siswa', 'education', 'member',
        ]);
    }

    public function assignedWork(): HasMany
    {
        return $this->hasMany(WorkAssignment::class, 'assigned_to');
    }

    public function createdWork(): HasMany
    {
        return $this->hasMany(WorkAssignment::class, 'created_by');
    }

    public function workSubmissions(): HasMany
    {
        return $this->hasMany(WorkSubmission::class, 'submitted_by');
    }

    public function hasActiveWorkAssignment(): bool
    {
        return $this->assignedWork()
            ->whereIn('status', ['open', 'in_progress', 'submitted', 'revision', 'cancel_requested'])
            ->exists();
    }
}
