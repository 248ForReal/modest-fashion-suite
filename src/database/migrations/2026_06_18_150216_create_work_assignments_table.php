<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('garment_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('work_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('assigned_to')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('target_jumlah');
            $table->date('deadline')->nullable();
            $table->text('instruksi')->nullable();
            $table->enum('status', [
                'open',
                'in_progress',
                'submitted',
                'revision',
                'approved',
                'rejected',
            ])->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_assignments');
    }
};
