<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('protocol', 30)->unique();
            $table->enum('type', ['maintenance', 'repair', 'inspection']);
            $table->enum('location_type', ['unit', 'common_area']);
            $table->string('location_detail')->nullable();
            $table->string('title');
            $table->text('description');
            $table->enum('urgency', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->date('preferred_date')->nullable();
            $table->time('preferred_time_start')->nullable();
            $table->time('preferred_time_end')->nullable();
            $table->text('availability_notes')->nullable();
            $table->enum('status', [
                'open',
                'dispatched',
                'in_progress',
                'resolved',
                'unresolved',
                'cancelled',
            ])->default('open');
            $table->text('resolution_notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('charge_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('reimbursement_total', 12, 2)->default(0);
            $table->boolean('whatsapp_notify')->default(true);
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['condominium_id', 'status']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('service_order_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->boolean('is_internal')->default(false);
            $table->timestamps();

            $table->index(['service_order_id', 'created_at']);
        });

        Schema::create('service_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['material', 'service']);
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total', 12, 2);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        if (Schema::hasColumn('charges', 'generated_by')) {
            DB::statement("ALTER TABLE charges MODIFY generated_by ENUM('manual','fee','reservation','import','fine','service_order') NOT NULL DEFAULT 'manual'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_items');
        Schema::dropIfExists('service_order_messages');
        Schema::dropIfExists('service_orders');

        if (Schema::hasColumn('charges', 'generated_by')) {
            DB::statement("ALTER TABLE charges MODIFY generated_by ENUM('manual','fee','reservation','import','fine') NOT NULL DEFAULT 'manual'");
        }
    }
};
