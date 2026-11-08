<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('nomor', 30)->unique(); // TXN-20260823-0001
            $table->enum('tipe', [
                'masuk_stok',      // syncUnits tambah unit baru
                'pindah_gudang',   // unit dipindah antar gudang
                'distribusi',      // distribusi batch dari halaman Distribusi
                'update_status',   // perubahan status unit
                'hapus_unit',      // unit dihapus
            ]);
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();       // gudang sumber
            $table->foreignId('warehouse_tujuan_id')->nullable()->constrained('warehouses')->nullOnDelete(); // hanya untuk pindah/distribusi
            $table->string('unit_code', 50)->nullable();  // untuk transaksi per-unit
            $table->integer('jumlah')->default(0);         // jumlah unit terlibat
            $table->string('status_dari', 30)->nullable(); // untuk update_status
            $table->string('status_ke', 30)->nullable();   // untuk update_status
            $table->string('catatan', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->index(['warehouse_id', 'created_at']);
            $table->index(['tipe', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
