<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_sections', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique(); // hero, about, dna, craft, collections, process, partners, testimonials, contact
            $table->string('display_type', 20)->default('static'); // static, grid, carousel
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('urutan')->default(0);

            // Konten umum section (dipakai sebagian besar section)
            $table->string('eyebrow_en')->nullable();
            $table->string('eyebrow_id')->nullable();
            $table->string('title_en')->nullable();
            $table->string('title_id')->nullable();
            $table->text('subtitle_en')->nullable();
            $table->text('subtitle_id')->nullable();
            $table->longText('body_en')->nullable();
            $table->longText('body_id')->nullable();

            // CTA opsional
            $table->string('cta_label_en')->nullable();
            $table->string('cta_label_id')->nullable();
            $table->string('cta_url')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_sections');
    }
};
