<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('condominiums', function (Blueprint $table) {
            $table->string('ai_provider', 32)->nullable()->after('label_ocr_engine');
            $table->string('ai_model', 120)->nullable()->after('ai_provider');
        });

        // Compatibilidade: condomínios existentes continuam em OpenAI (comportamento atual).
        DB::table('condominiums')->whereNull('ai_provider')->update([
            'ai_provider' => 'openai',
        ]);

        Schema::table('ai_financial_consultations', function (Blueprint $table) {
            $table->string('provider', 32)->nullable()->after('indicators_hash');
        });
    }

    public function down(): void
    {
        Schema::table('ai_financial_consultations', function (Blueprint $table) {
            $table->dropColumn('provider');
        });

        Schema::table('condominiums', function (Blueprint $table) {
            $table->dropColumn(['ai_provider', 'ai_model']);
        });
    }
};
