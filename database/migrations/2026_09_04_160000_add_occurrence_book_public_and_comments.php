<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('condominiums', function (Blueprint $table) {
            $table->boolean('occurrence_book_public_enabled')->default(false)->after('restrict_defaulters');
        });

        Schema::table('occurrence_book_entries', function (Blueprint $table) {
            $table->text('syndic_comment')->nullable()->after('acknowledgment_note');
            $table->boolean('show_syndic_comment_publicly')->default(false)->after('syndic_comment');
            $table->timestamp('syndic_commented_at')->nullable()->after('show_syndic_comment_publicly');
            $table->foreignId('syndic_commented_by')->nullable()->after('syndic_commented_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('occurrence_book_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('syndic_commented_by');
            $table->dropColumn([
                'syndic_comment',
                'show_syndic_comment_publicly',
                'syndic_commented_at',
            ]);
        });

        Schema::table('condominiums', function (Blueprint $table) {
            $table->dropColumn('occurrence_book_public_enabled');
        });
    }
};
