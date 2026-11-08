<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 20);
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // Seed default sizes
        $now = now();
        DB::table('product_sizes')->insert([
            ['nama' => 'S',         'urutan' => 1, 'aktif' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'M',         'urutan' => 2, 'aktif' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'L',         'urutan' => 3, 'aktif' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'XL',        'urutan' => 4, 'aktif' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'XXL',       'urutan' => 5, 'aktif' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Free Size', 'urutan' => 6, 'aktif' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('product_sizes');
    }
};
