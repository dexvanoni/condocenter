<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Impacto: migration aditiva e reversível. Não altera nem remove dados existentes.
     * Encomendas antigas ficam com pickup_code_hash null (retirada legada sem senha).
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('pickup_code_hash')->nullable()->after('notes');
            $table->string('label_image_path')->nullable()->after('pickup_code_hash');
            $table->text('ocr_text')->nullable()->after('label_image_path');
            $table->decimal('ocr_confidence', 5, 4)->nullable()->after('ocr_text');
            $table->string('identification_method', 20)->nullable()->after('ocr_confidence');
            $table->decimal('identification_confidence', 5, 4)->nullable()->after('identification_method');
            $table->foreignId('identified_resident_id')
                ->nullable()
                ->after('identification_confidence')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('whatsapp_delivery_status', 20)
                ->nullable()
                ->after('identified_resident_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('identified_resident_id');
            $table->dropColumn([
                'pickup_code_hash',
                'label_image_path',
                'ocr_text',
                'ocr_confidence',
                'identification_method',
                'identification_confidence',
                'whatsapp_delivery_status',
            ]);
        });
    }
};
