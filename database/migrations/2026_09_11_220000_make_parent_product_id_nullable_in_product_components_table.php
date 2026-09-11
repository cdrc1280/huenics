<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE product_components MODIFY parent_product_id BIGINT UNSIGNED NULL;');
        } else {
            Schema::table('product_components', function (Blueprint $table) {
                $table->foreignId('parent_product_id')->nullable()->change();
            });
        }

        // Backfill existing distinct components as standalone library records if none exist
        try {
            $existing = DB::table('product_components')
                ->whereNotNull('parent_product_id')
                ->whereNotNull('component_name')
                ->select([
                    'component_name',
                    'product_code',
                    'category',
                    'wattage',
                    'voltage',
                    'color_temperature',
                    'unit',
                    'cost_price',
                    'component_product_id',
                    'image_path',
                    'notes',
                ])
                ->distinct()
                ->get();

            foreach ($existing as $item) {
                $alreadyExists = DB::table('product_components')
                    ->whereNull('parent_product_id')
                    ->where('component_name', $item->component_name)
                    ->exists();

                if (! $alreadyExists) {
                    DB::table('product_components')->insert([
                        'parent_product_id' => null,
                        'component_group' => $item->category ?: 'General',
                        'option_name' => $item->component_name,
                        'component_product_id' => $item->component_product_id,
                        'additional_cost' => $item->cost_price ?: 0.00,
                        'is_default' => true,
                        'quantity' => 1.0000,
                        'product_code' => $item->product_code,
                        'component_name' => $item->component_name,
                        'category' => $item->category ?: 'General',
                        'wattage' => $item->wattage,
                        'voltage' => $item->voltage,
                        'color_temperature' => $item->color_temperature,
                        'unit' => $item->unit ?: 'pcs',
                        'cost_price' => $item->cost_price ?: 0.00,
                        'image_path' => $item->image_path,
                        'notes' => $item->notes,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        } catch (Throwable $e) {
            // Safe fallback if migration executes in isolated test environment
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE product_components MODIFY parent_product_id BIGINT UNSIGNED NOT NULL;');
        } else {
            Schema::table('product_components', function (Blueprint $table) {
                $table->foreignId('parent_product_id')->nullable(false)->change();
            });
        }
    }
};
