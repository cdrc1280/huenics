<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The tables that support soft deleting.
     */
    protected array $tables = [
        'users',
        'vendors',
        'projects',
        'products',
        'product_aliases',
        'quotations',
        'purchase_orders',
        'delivery_receipts',
        'sales_invoices',
        'transactions',
        'documents',
        'inventory_items',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                    if (!Schema::hasColumn($table, 'deleted_at')) {
                        $tableBlueprint->softDeletes();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                    if (Schema::hasColumn($table, 'deleted_at')) {
                        $tableBlueprint->dropSoftDeletes();
                    }
                });
            }
        }
    }
};
