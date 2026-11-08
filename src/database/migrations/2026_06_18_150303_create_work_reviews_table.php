<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewed_by')->constrained('users')->restrictOnDelete();
            $table->enum('status', ['approved', 'revision', 'rejected']);
            $table->unsignedTinyInteger('skor_kualitas')->nullable(); // 1-5
            $table->text('catatan_review')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_reviews');
    }
};
