<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code', 20)->unique();
            $table->foreignId('garment_category_id')->constrained()->restrictOnDelete();
            $table->string('nama');
            $table->unsignedInteger('jumlah')->default(0);
            $table->decimal('harga', 12, 2)->default(0);
            $table->string('lokasi')->nullable();
            $table->string('video_url')->nullable();
            $table->enum('status', ['aktif', 'nonaktif', 'habis'])->default('aktif');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('product_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('photo_path');
            $table->tinyInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_photos');
        Schema::dropIfExists('products');
    }
};
