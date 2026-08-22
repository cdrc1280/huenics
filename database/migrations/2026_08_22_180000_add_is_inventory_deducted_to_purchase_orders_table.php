<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchase_orders') && !Schema::hasColumn('purchase_orders', 'is_inventory_deducted')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->boolean('is_inventory_deducted')->default(false)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchase_orders') && Schema::hasColumn('purchase_orders', 'is_inventory_deducted')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropColumn('is_inventory_deducted');
            });
        }
    }
};
