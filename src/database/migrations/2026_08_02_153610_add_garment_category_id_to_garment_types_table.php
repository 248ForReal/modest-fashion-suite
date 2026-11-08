<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Isi tabel garment_categories dari data yang sudah ada
        $categories = DB::table('garment_types')->distinct()->pluck('kategori');
        foreach ($categories as $nama) {
            DB::table('garment_categories')->insertOrIgnore(['nama' => $nama, 'created_at' => now(), 'updated_at' => now()]);
        }

        // 2. Tambah kolom nullable dulu (tanpa constraint)
        Schema::table('garment_types', function (Blueprint $table) {
            $table->unsignedBigInteger('garment_category_id')->nullable()->after('id');
        });

        // 3. Isi foreign key berdasarkan nilai kategori lama
        DB::statement('
            UPDATE garment_types gt
            JOIN garment_categories gc ON gc.nama = gt.kategori
            SET gt.garment_category_id = gc.id
        ');

        // 4. Ubah jadi not null dan tambah foreign key
        Schema::table('garment_types', function (Blueprint $table) {
            $table->unsignedBigInteger('garment_category_id')->nullable(false)->change();
            $table->foreign('garment_category_id')->references('id')->on('garment_categories')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('garment_types', function (Blueprint $table) {
            $table->dropForeign(['garment_category_id']);
            $table->dropColumn('garment_category_id');
        });

        Schema::dropIfExists('garment_categories');
    }
};
