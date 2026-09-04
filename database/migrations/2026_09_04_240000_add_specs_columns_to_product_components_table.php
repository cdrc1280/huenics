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
        Schema::table('product_components', function (Blueprint $table) {
            if (!Schema::hasColumn('product_components', 'quantity')) {
                $table->decimal('quantity', 12, 4)->default(1.0000)->after('is_default');
            }
            if (!Schema::hasColumn('product_components', 'product_code')) {
                $table->string('product_code', 100)->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('product_components', 'component_name')) {
                $table->string('component_name', 255)->nullable()->after('product_code');
            }
            if (!Schema::hasColumn('product_components', 'category')) {
                $table->string('category', 100)->nullable()->after('component_name');
            }
            if (!Schema::hasColumn('product_components', 'wattage')) {
                $table->string('wattage', 100)->nullable()->after('category');
            }
            if (!Schema::hasColumn('product_components', 'voltage')) {
                $table->string('voltage', 100)->nullable()->after('wattage');
            }
            if (!Schema::hasColumn('product_components', 'color_temperature')) {
                $table->string('color_temperature', 100)->nullable()->after('voltage');
            }
            if (!Schema::hasColumn('product_components', 'unit')) {
                $table->string('unit', 50)->default('pcs')->after('color_temperature');
            }
            if (!Schema::hasColumn('product_components', 'cost_price')) {
                $table->decimal('cost_price', 12, 2)->default(0.00)->after('unit');
            }
            if (!Schema::hasColumn('product_components', 'image_path')) {
                $table->string('image_path', 255)->nullable()->after('cost_price');
            }
            if (!Schema::hasColumn('product_components', 'notes')) {
                $table->text('notes')->nullable()->after('image_path');
            }

            $table->string('component_group', 100)->nullable()->change();
            $table->string('option_name', 100)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_components', function (Blueprint $table) {
            $columns = [
                'quantity',
                'product_code',
                'component_name',
                'category',
                'wattage',
                'voltage',
                'color_temperature',
                'unit',
                'cost_price',
                'image_path',
                'notes',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('product_components', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
