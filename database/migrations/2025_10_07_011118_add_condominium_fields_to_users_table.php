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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('condominium_id')->nullable()->after('id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->after('condominium_id')->constrained()->onDelete('set null');
            $table->string('phone')->nullable()->after('email');
            $table->string('cpf')->nullable()->unique()->after('phone');
            $table->string('photo')->nullable()->after('cpf');
            $table->string('qr_code')->nullable()->unique()->after('photo'); // QR Code único
            $table->boolean('is_active')->default(true)->after('qr_code');
            $table->softDeletes();
            
            $table->index('condominium_id');
            $table->index('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['condominium_id']);
            $table->dropForeign(['unit_id']);
            $table->dropColumn([
                'condominium_id',
                'unit_id',
                'phone',
                'cpf',
                'photo',
                'qr_code',
                'is_active',
                'deleted_at'
            ]);
        });
    }
};
