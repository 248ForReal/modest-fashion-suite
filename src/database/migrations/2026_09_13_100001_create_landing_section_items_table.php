<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_section_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('urutan')->default(0);

            $table->string('image_path')->nullable();

            $table->string('label_en')->nullable();   // no. urut / kategori / nama kontak dsb
            $table->string('label_id')->nullable();
            $table->string('title_en')->nullable();
            $table->string('title_id')->nullable();
            $table->text('body_en')->nullable();
            $table->text('body_id')->nullable();

            $table->string('link_url')->nullable();   // opsional: link per item (mis. partner website)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_section_items');
    }
};
