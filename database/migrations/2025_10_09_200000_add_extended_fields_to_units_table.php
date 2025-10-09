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
        Schema::table('units', function (Blueprint $table) {
            // Endereço completo
            $table->string('cep', 9)->nullable()->after('type');
            $table->string('logradouro')->nullable()->after('cep');
            $table->string('numero')->nullable()->after('logradouro');
            $table->string('complemento')->nullable()->after('numero');
            $table->string('bairro')->nullable()->after('complemento');
            $table->string('cidade')->nullable()->after('bairro');
            $table->string('estado', 2)->nullable()->after('cidade');
            
            // Situação da unidade
            $table->enum('situacao', ['habitado', 'fechado', 'indisponivel', 'em_obra'])
                  ->default('habitado')
                  ->after('type');
            
            // Características
            $table->integer('num_quartos')->nullable()->after('area');
            $table->integer('num_banheiros')->nullable()->after('num_quartos');
            
            // Foto da unidade
            $table->string('foto')->nullable()->after('num_banheiros');
            
            // Dívidas
            $table->boolean('possui_dividas')->default(false)->after('foto');
            
            // Índices
            $table->index('cep');
            $table->index('situacao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropIndex(['cep']);
            $table->dropIndex(['situacao']);
            
            $table->dropColumn([
                'cep',
                'logradouro',
                'numero',
                'complemento',
                'bairro',
                'cidade',
                'estado',
                'situacao',
                'num_quartos',
                'num_banheiros',
                'foto',
                'possui_dividas',
            ]);
        });
    }
};

