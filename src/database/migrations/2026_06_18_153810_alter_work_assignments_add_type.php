<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_assignments', function (Blueprint $table) {
            // tambah kolom type setelah id
            $table->enum('type', ['personal', 'pool'])->default('personal')->after('id');
            // buat assigned_to nullable (pool tidak punya assignee awal)
            $table->foreignId('assigned_to')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('work_assignments', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->foreignId('assigned_to')->nullable(false)->change();
        });
    }
};
