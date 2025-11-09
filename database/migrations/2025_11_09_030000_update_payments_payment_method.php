<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cash','pix','bank_transfer','credit_card','debit_card','boleto','payroll','other') NULL DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cash','pix','bank_transfer','credit_card','debit_card','boleto','other') NULL DEFAULT NULL");
    }
};

