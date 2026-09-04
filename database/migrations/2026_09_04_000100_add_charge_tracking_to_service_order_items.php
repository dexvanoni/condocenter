<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_order_items', function (Blueprint $table) {
            $table->foreignId('charge_id')->nullable()->after('service_order_id')->constrained()->nullOnDelete();
        });

        if (!Schema::hasColumn('charges', 'service_order_id')) {
            Schema::table('charges', function (Blueprint $table) {
                $table->foreignId('service_order_id')->nullable()->after('unit_id')->constrained()->nullOnDelete();
            });
        }

        DB::table('service_orders')
            ->whereNotNull('charge_id')
            ->orderBy('id')
            ->get()
            ->each(function ($order) {
                DB::table('charges')
                    ->where('id', $order->charge_id)
                    ->update(['service_order_id' => $order->id]);

                DB::table('service_order_items')
                    ->where('service_order_id', $order->id)
                    ->whereNull('charge_id')
                    ->update(['charge_id' => $order->charge_id]);
            });
    }

    public function down(): void
    {
        Schema::table('service_order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('charge_id');
        });

        if (Schema::hasColumn('charges', 'service_order_id')) {
            Schema::table('charges', function (Blueprint $table) {
                $table->dropConstrainedForeignId('service_order_id');
            });
        }
    }
};
