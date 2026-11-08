<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_assignments', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('work_type_id')->constrained('products')->nullOnDelete();
            $table->integer('hasil_jumlah')->nullable()->after('product_id')->comment('Jumlah unit yang dihasilkan dari tugas ini');
        });
    }

    public function down(): void
    {
        Schema::table('work_assignments', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'hasil_jumlah']);
        });
    }
};
