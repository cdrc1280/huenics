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
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('location')->nullable()->after('unit')->index();
            $table->string('supplier_name')->nullable()->after('location')->index();
            $table->string('po_number', 100)->nullable()->after('supplier_name')->index();
            $table->string('customer_name')->nullable()->after('po_number')->index();
            $table->string('project_name')->nullable()->after('customer_name')->index();
            $table->date('date_released')->nullable()->after('project_name');
            $table->date('inbound_date')->nullable()->after('date_released');
            $table->text('remarks')->nullable()->after('inbound_date');
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->string('po_number', 100)->nullable()->after('quantity')->index();
            $table->string('supplier_name')->nullable()->after('po_number')->index();
            $table->string('customer_name')->nullable()->after('supplier_name')->index();
            $table->string('project_name')->nullable()->after('customer_name')->index();
            $table->string('location')->nullable()->after('project_name')->index();
            $table->date('date_released')->nullable()->after('location');
            $table->decimal('transit_in', 12, 4)->nullable()->after('date_released');
            $table->decimal('transit_out', 12, 4)->nullable()->after('transit_in');
            $table->decimal('balance_after', 12, 4)->nullable()->after('transit_out');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'po_number',
                'supplier_name',
                'customer_name',
                'project_name',
                'location',
                'date_released',
                'transit_in',
                'transit_out',
                'balance_after',
            ]);
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn([
                'location',
                'supplier_name',
                'po_number',
                'customer_name',
                'project_name',
                'date_released',
                'inbound_date',
                'remarks',
            ]);
        });
    }
};
