<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accountability_report_uploads', function (Blueprint $table) {
            $table->enum('council_status', ['pending', 'approved'])
                ->default('pending')
                ->after('notes');
            $table->foreignId('reviewed_by')->nullable()->after('council_status')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });

        DB::table('accountability_report_uploads')->update([
            'council_status' => 'approved',
            'reviewed_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('accountability_report_uploads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['council_status', 'reviewed_at']);
        });
    }
};
