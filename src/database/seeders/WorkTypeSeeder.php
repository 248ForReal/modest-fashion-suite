<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkTypeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // Kategori jahit — total bobot = 100%
            ['nama' => 'Menjahit',       'kategori_nilai' => 'jahit',  'bobot_persen' => 65.00],
            ['nama' => 'Jahit Pinggir',  'kategori_nilai' => 'jahit',  'bobot_persen' => 20.00],
            ['nama' => 'Lobang Kancing', 'kategori_nilai' => 'jahit',  'bobot_persen' => 15.00],

            // Kategori desain — total bobot = 100%
            ['nama' => 'Desain',         'kategori_nilai' => 'desain', 'bobot_persen' => 60.00],
            ['nama' => 'Mempayet',       'kategori_nilai' => 'desain', 'bobot_persen' => 25.00],
            ['nama' => 'Finishing',      'kategori_nilai' => 'desain', 'bobot_persen' => 15.00],

            // Kategori pola — total bobot = 100%
            ['nama' => 'Potong Pola',    'kategori_nilai' => 'pola',   'bobot_persen' => 80.00],
            ['nama' => 'Packaging',      'kategori_nilai' => 'pola',   'bobot_persen' => 20.00],
        ];

        foreach ($data as $row) {
            DB::table('work_types')->insertOrIgnore(array_merge($row, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
