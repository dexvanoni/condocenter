<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('access_authorizations', function (Blueprint $table) {
            $table->string('visitor_document')->nullable()->after('visitor_name');
            $table->boolean('never_expires')->default(false)->after('authorization_type');
            $table->dateTime('scheduled_at')->nullable()->change();
            $table->dateTime('expires_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('access_authorizations', function (Blueprint $table) {
            $table->dropColumn(['visitor_document', 'never_expires']);
            $table->dateTime('scheduled_at')->nullable(false)->change();
            $table->dateTime('expires_at')->nullable(false)->change();
        });
    }
};
