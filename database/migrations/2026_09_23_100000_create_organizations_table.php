<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('type', 32); // condominium | management_company
            $table->string('legal_name');
            $table->string('trade_name')->nullable();
            $table->string('document', 18)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('address')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip_code', 12)->nullable();
            $table->string('status', 32)->default('active'); // active|suspended|blocked
            $table->timestamp('trial_ends_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('status');
            $table->index('document');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
