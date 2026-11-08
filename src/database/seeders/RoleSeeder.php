<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'attendance.create', 'attendance.view_own', 'attendance.view_all', 'attendance.manage',
            'leave.create', 'leave.approve',
            'profile.edit_own', 'profile.view_all',
            'design.input', 'design.take',
            'finance.input', 'production.input', 'store.manage',
            'user.manage', 'role.manage', 'settings.manage',
            'work.assign', 'work.review', 'work.view_all', 'work.view_own', 'work.cancel_approve',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [
            'super_admin' => $permissions,
            'admin' => [
                'attendance.create', 'attendance.view_own', 'attendance.view_all', 'attendance.manage',
                'leave.create', 'leave.approve',
                'profile.edit_own', 'profile.view_all',
                'user.manage', 'settings.manage',
                'work.assign', 'work.review', 'work.view_all', 'work.cancel_approve',
            ],
            'designer' => ['attendance.create', 'attendance.view_own', 'leave.create', 'profile.edit_own', 'design.input', 'work.assign', 'work.review', 'work.view_all', 'work.cancel_approve'],
            'keuangan' => ['attendance.create', 'attendance.view_own', 'leave.create', 'profile.edit_own', 'finance.input'],
            'marketing' => ['attendance.create', 'attendance.view_own', 'leave.create', 'profile.edit_own', 'store.manage'],
            'team_leader' => ['attendance.create', 'attendance.view_own', 'leave.create', 'profile.edit_own', 'work.assign', 'work.review', 'work.view_all', 'work.cancel_approve'],
            'tailor' => ['attendance.create', 'attendance.view_own', 'leave.create', 'profile.edit_own', 'design.take', 'production.input', 'work.assign', 'work.review', 'work.view_all', 'work.cancel_approve'],
            'support' => ['attendance.create', 'attendance.view_own', 'leave.create', 'profile.edit_own', 'design.take'],
            'education' => ['attendance.create', 'attendance.view_own', 'profile.edit_own', 'work.view_own'],
            'siswa' => ['attendance.create', 'attendance.view_own', 'profile.edit_own', 'work.view_own'],
            'magang' => ['attendance.create', 'attendance.view_own', 'profile.edit_own', 'work.view_own'],
            'member' => ['profile.edit_own'],
            'guest' => [],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }
    }
}
