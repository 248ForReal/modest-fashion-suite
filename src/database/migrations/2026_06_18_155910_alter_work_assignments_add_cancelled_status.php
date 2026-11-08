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
        // Tambah status cancel_requested dan cancelled ke enum
        // MySQL: harus modify kolom enum secara eksplisit
        \Illuminate\Support\Facades\DB::statement(
            "ALTER TABLE work_assignments MODIFY COLUMN status ENUM(
                'open','in_progress','submitted','revision','approved','rejected',
                'cancel_requested','cancelled'
            ) NOT NULL DEFAULT 'open'"
        );

        // Tambah kolom cooldown — waktu selesai (approved/rejected/cancelled)
        Schema::table('work_assignments', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('work_assignments', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });

        \Illuminate\Support\Facades\DB::statement(
            "ALTER TABLE work_assignments MODIFY COLUMN status ENUM(
                'open','in_progress','submitted','revision','approved','rejected'
            ) NOT NULL DEFAULT 'open'"
        );
    }
};
