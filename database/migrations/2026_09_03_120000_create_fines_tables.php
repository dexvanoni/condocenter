<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 40);
            $table->text('motivo');
            $table->string('enquadramento');
            $table->decimal('amount', 12, 2);
            $table->date('due_date');
            $table->timestamp('applied_at');
            $table->foreignId('applied_by')->constrained('users')->restrictOnDelete();
            $table->enum('status', ['issued', 'cancelled'])->default('issued');
            $table->text('notes')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['condominium_id', 'reference']);
            $table->index(['condominium_id', 'status']);
            $table->index(['condominium_id', 'applied_at']);
        });

        Schema::create('fine_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->foreignId('notified_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('charge_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['fine_id', 'user_id']);
        });

        if (Schema::hasColumn('charges', 'generated_by')) {
            DB::statement("ALTER TABLE charges MODIFY generated_by ENUM('manual','fee','reservation','import','fine') NOT NULL DEFAULT 'manual'");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('charges', 'generated_by')) {
            DB::statement("ALTER TABLE charges MODIFY generated_by ENUM('manual','fee','reservation','import') NOT NULL DEFAULT 'manual'");
        }

        Schema::dropIfExists('fine_recipients');
        Schema::dropIfExists('fines');
    }
};
