<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('occurrence_book_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 32);
            $table->string('title');
            $table->text('body');
            $table->boolean('notify_whatsapp')->default(false);
            $table->timestamp('whatsapp_notified_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('acknowledgment_note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['condominium_id', 'type']);
            $table->index(['condominium_id', 'created_at']);
            $table->index(['condominium_id', 'acknowledged_at']);
            $table->index(['user_id', 'created_at']);
        });

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ([
            'create_occurrence_book',
            'manage_occurrence_book',
            'export_occurrence_book',
        ] as $permission) {
            Permission::firstOrCreate(['name' => $permission], ['guard_name' => 'web']);
        }

        Role::findByName('Síndico')?->givePermissionTo([
            'manage_occurrence_book',
            'export_occurrence_book',
        ]);

        Role::findByName('Morador')?->givePermissionTo([
            'create_occurrence_book',
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('occurrence_book_entries');
    }
};
