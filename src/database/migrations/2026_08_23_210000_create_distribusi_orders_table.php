<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribusi_orders', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->unique();                         // DIST-20260823-0001
            $table->foreignId('warehouse_asal_id')->constrained('warehouses');
            $table->foreignId('warehouse_tujuan_id')->constrained('warehouses');
            $table->integer('total_unit');
            $table->string('catatan')->nullable();
            $table->string('foto_bukti')->nullable();                  // path foto
            // status untuk modul penerimaan (nonaktif by default)
            $table->enum('status', ['selesai', 'menunggu_penerimaan', 'diterima'])->default('selesai');
            $table->foreignId('diterima_oleh')->nullable()->constrained('users');
            $table->timestamp('diterima_at')->nullable();
            $table->string('catatan_penerimaan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // Link tiap baris produk ke order distribusi
        Schema::create('distribusi_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribusi_order_id')->constrained('distribusi_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->integer('jumlah');
            $table->string('posisi_tujuan')->nullable();               // posisi rak di gudang tujuan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribusi_order_items');
        Schema::dropIfExists('distribusi_orders');
    }
};
