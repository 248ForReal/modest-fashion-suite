<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submitted_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('jumlah_selesai');
            $table->text('catatan')->nullable();
            $table->string('foto_path')->nullable();
            $table->decimal('nilai_poin', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_submissions');
    }
};
