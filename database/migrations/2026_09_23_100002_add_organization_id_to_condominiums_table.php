<?php

use App\Models\Condominium;
use App\Models\Organization;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('condominiums', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('id')
                ->constrained('organizations')
                ->nullOnDelete();
        });

        // Backfill: one direct organization per existing condominium (additive, reversible).
        Condominium::query()
            ->withTrashed()
            ->whereNull('organization_id')
            ->orderBy('id')
            ->chunkById(100, function ($condominiums) {
                foreach ($condominiums as $condominium) {
                    $organization = Organization::query()->create([
                        'type' => Organization::TYPE_CONDOMINIUM,
                        'legal_name' => $condominium->name,
                        'trade_name' => $condominium->name,
                        'document' => $condominium->cnpj,
                        'email' => $condominium->email,
                        'phone' => $condominium->phone,
                        'address' => $condominium->address,
                        'neighborhood' => $condominium->neighborhood,
                        'city' => $condominium->city,
                        'state' => $condominium->state,
                        'zip_code' => $condominium->zip_code,
                        'status' => $condominium->is_active
                            ? Organization::STATUS_ACTIVE
                            : Organization::STATUS_SUSPENDED,
                        'notes' => 'Criada automaticamente no backfill multi-tenant.',
                    ]);

                    $condominium->forceFill([
                        'organization_id' => $organization->id,
                    ])->saveQuietly();
                }
            });
    }

    public function down(): void
    {
        Schema::table('condominiums', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
        });
    }
};
