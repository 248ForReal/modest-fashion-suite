<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('size_guides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('garment_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_size_id')->constrained('product_sizes')->cascadeOnDelete();
            // Dimensi dalam JSON — fleksibel per kategori
            // Contoh: {"lingkar_dada":96,"panjang":68,"lingkar_pinggang":80,"lebar_bahu":44}
            $table->json('dimensi')->nullable();
            $table->timestamps();

            $table->unique(['garment_category_id', 'product_size_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('size_guides');
    }
};
