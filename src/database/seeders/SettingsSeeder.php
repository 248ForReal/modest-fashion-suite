<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'office_latitude',   'value' => '-6.200000',  'description' => 'Latitude lokasi kantor'],
            ['key' => 'office_longitude',  'value' => '106.816666', 'description' => 'Longitude lokasi kantor'],
            ['key' => 'attendance_radius', 'value' => '100',        'description' => 'Radius absen dari kantor (meter)'],
            ['key' => 'work_start_time',   'value' => '08:00',      'description' => 'Jam mulai kerja'],
            ['key' => 'work_end_time',     'value' => '17:00',      'description' => 'Jam selesai kerja'],
            ['key' => 'attendance_module',      'value' => 'true', 'description' => 'Aktifkan/nonaktifkan modul absensi'],
            ['key' => 'work_cooldown_minutes',  'value' => '2',    'description' => 'Cooldown (menit) setelah tugas selesai sebelum bisa ambil tugas baru'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
