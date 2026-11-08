<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates fictional demo accounts for local development only.
 * It does nothing outside the "local" and "testing" environments.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('AdminUserSeeder skipped: create your first admin with `php artisan tinker`.');

            return;
        }

        $accounts = [
            ['Demo Admin',  'admin@example.com',  'super_admin'],
            ['Demo Tailor', 'tailor@example.com', 'tailor'],
            ['Demo Intern', 'intern@example.com', 'magang'],
        ];

        foreach ($accounts as [$name, $email, $role]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => 'password', 'status' => 'active'],
            );
            $user->syncRoles([$role]);
        }
    }
}
