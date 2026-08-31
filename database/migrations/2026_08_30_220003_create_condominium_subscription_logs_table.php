<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condominium_subscription_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_subscription_id');
            $table->foreign('condominium_subscription_id', 'cs_logs_subscription_fk')
                ->references('id')
                ->on('condominium_subscriptions')
                ->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condominium_subscription_logs');
    }
};
