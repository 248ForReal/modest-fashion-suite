<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('period', 7); // YYYY-MM

            // Kehadiran & hasil
            $table->unsignedSmallInteger('work_days')->default(0);
            $table->unsignedSmallInteger('absent_days')->default(0);
            $table->unsignedInteger('garment_count')->default(0);

            // A. Benefit
            $table->unsignedBigInteger('piece_earnings')->default(0);   // Hasil Kerja
            $table->unsignedBigInteger('meal_allowance')->default(0);   // Uang makan
            $table->unsignedBigInteger('allowance')->default(0);        // Tunjangan
            $table->unsignedBigInteger('performance_bonus')->default(0); // Bonus kinerja

            // B. Bantuan
            $table->unsignedBigInteger('employer_bpjs')->default(0);    // BPJS TK (ditanggung perusahaan, tidak masuk total)
            $table->unsignedBigInteger('other_bonus')->default(0);      // Bonus lainnya
            $table->unsignedBigInteger('pocket_money')->default(0);     // Jajan

            // C. Lainnya & pengurangan
            $table->unsignedBigInteger('other_income')->default(0);
            $table->unsignedBigInteger('deduction')->default(0);

            $table->unsignedBigInteger('total_income')->default(0);
            $table->bigInteger('take_home_pay')->default(0);

            // Rekening (snapshot saat slip dibuat)
            $table->string('bank_name')->nullable();
            $table->string('bank_account', 50)->nullable();
            $table->date('transfer_date')->nullable();

            $table->string('status', 10)->default('draft'); // draft | final
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'period']);
            $table->index('period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
