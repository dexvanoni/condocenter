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
            // Telefones múltiplos
            $table->string('telefone_residencial')->nullable()->after('phone');
            $table->string('telefone_celular')->nullable()->after('telefone_residencial');
            $table->string('telefone_comercial')->nullable()->after('telefone_celular');
            
            // Documentos
            $table->string('cnh')->nullable()->after('cpf');
            
            // Dados pessoais
            $table->date('data_nascimento')->nullable()->after('cnh');
            $table->date('data_entrada')->nullable()->after('data_nascimento');
            $table->date('data_saida')->nullable()->after('data_entrada');
            
            // Informações especiais
            $table->boolean('necessita_cuidados_especiais')->default(false)->after('data_saida');
            $table->text('descricao_cuidados_especiais')->nullable()->after('necessita_cuidados_especiais');
            
            // Informações profissionais
            $table->string('local_trabalho')->nullable()->after('descricao_cuidados_especiais');
            $table->string('contato_comercial')->nullable()->after('local_trabalho');
            
            // Relacionamento agregado-morador
            $table->foreignId('morador_vinculado_id')
                  ->nullable()
                  ->after('unit_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Senha temporária
            $table->boolean('senha_temporaria')->default(true)->after('password');
            
            // Dívidas
            $table->boolean('possui_dividas')->default(false)->after('is_active');
            
            // Índices
            $table->index('morador_vinculado_id');
            $table->index('data_nascimento');
            $table->index('data_entrada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['morador_vinculado_id']);
            
            $table->dropIndex(['morador_vinculado_id']);
            $table->dropIndex(['data_nascimento']);
            $table->dropIndex(['data_entrada']);
            
            $table->dropColumn([
                'telefone_residencial',
                'telefone_celular',
                'telefone_comercial',
                'cnh',
                'data_nascimento',
                'data_entrada',
                'data_saida',
                'necessita_cuidados_especiais',
                'descricao_cuidados_especiais',
                'local_trabalho',
                'contato_comercial',
                'morador_vinculado_id',
                'senha_temporaria',
                'possui_dividas',
            ]);
        });
    }
};

