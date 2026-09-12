<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reservations') || Schema::hasColumn('reservations', 'recurring_reservation_id')) {
            return;
        }

        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('recurring_reservation_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('admin_action', ['created', 'edited', 'cancelled'])->nullable();
            $table->text('admin_reason')->nullable();
            $table->foreignId('admin_action_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('admin_action_at')->nullable();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('reservations') || ! Schema::hasColumn('reservations', 'recurring_reservation_id')) {
            return;
        }

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['recurring_reservation_id']);
            $table->dropForeign(['admin_action_by']);
            $table->dropColumn(['recurring_reservation_id', 'admin_action', 'admin_reason', 'admin_action_by', 'admin_action_at']);
        });
    }
};
