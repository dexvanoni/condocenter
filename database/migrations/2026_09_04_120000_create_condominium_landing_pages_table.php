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
        Schema::create('condominium_landing_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->unique()->constrained('condominiums')->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->boolean('is_published')->default(false);
            $table->string('hero_title')->nullable();
            $table->string('hero_subtitle')->nullable();
            $table->string('hero_image')->nullable();
            $table->json('hero_gallery')->nullable();
            $table->string('tagline')->nullable();
            $table->string('about_title')->nullable();
            $table->text('about_content')->nullable();
            $table->string('accent_color', 7)->default('#3866d2');
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_whatsapp')->nullable();
            $table->boolean('show_rides_feed')->default(true);
            $table->boolean('show_marketplace_feed')->default(true);
            $table->boolean('show_platform_news')->default(true);
            $table->boolean('show_announcements_feed')->default(true);
            $table->json('settings')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        $permission = Permission::firstOrCreate(
            ['name' => 'manage_landing_page', 'guard_name' => 'web']
        );

        Role::findByName('Síndico', 'web')?->givePermissionTo($permission);
        Role::findByName('Administrador', 'web')?->givePermissionTo($permission);
    }

    public function down(): void
    {
        Schema::dropIfExists('condominium_landing_pages');
    }
};
