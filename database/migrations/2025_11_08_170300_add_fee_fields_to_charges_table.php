<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('charges', function (Blueprint $table) {
            $table->foreignId('fee_id')->nullable()->after('unit_id')->constrained('fees')->nullOnDelete();
            $table->enum('generated_by', ['manual', 'fee', 'reservation', 'import'])->default('manual')->after('type');
            $table->string('recurrence_period')->nullable()->after('due_date');
            $table->json('metadata')->nullable()->after('pix_qrcode');
            $table->timestamp('first_reminder_sent_at')->nullable()->after('metadata');
            $table->timestamp('second_reminder_sent_at')->nullable()->after('first_reminder_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('charges', function (Blueprint $table) {
            $table->dropForeign(['fee_id']);
            $table->dropColumn([
                'fee_id',
                'generated_by',
                'recurrence_period',
                'metadata',
                'first_reminder_sent_at',
                'second_reminder_sent_at',
            ]);
        });
    }
};

