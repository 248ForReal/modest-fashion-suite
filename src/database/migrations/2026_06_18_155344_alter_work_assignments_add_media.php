<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('work_assignments', function (Blueprint $table) {
            $table->string('foto_instruksi')->nullable()->after('instruksi');
            $table->string('link_instruksi')->nullable()->after('foto_instruksi');
        });
    }

    public function down(): void
    {
        Schema::table('work_assignments', function (Blueprint $table) {
            $table->dropColumn(['foto_instruksi', 'link_instruksi']);
        });
    }
};
