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
        Schema::table('assemblies', function (Blueprint $table) {
            $table->enum('urgency', ['low', 'normal', 'high', 'critical'])
                ->default('normal')
                ->after('voting_type');
            $table->timestamp('voting_opens_at')
                ->nullable()
                ->after('scheduled_at');
            $table->timestamp('voting_closes_at')
                ->nullable()
                ->after('voting_opens_at');
            $table->boolean('allow_comments')
                ->default(false)
                ->after('allow_delegation');
            $table->json('voter_scope')
                ->nullable()
                ->after('allow_comments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assemblies', function (Blueprint $table) {
            $table->dropColumn([
                'urgency',
                'voting_opens_at',
                'voting_closes_at',
                'allow_comments',
                'voter_scope',
            ]);
        });
    }
};

