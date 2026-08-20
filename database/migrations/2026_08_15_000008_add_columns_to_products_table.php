<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_code', 100)->nullable()->unique()->after('id');
            $table->text('description')->nullable()->after('canonical_name');
            $table->decimal('base_cost_price', 12, 2)->default(0.00)->after('default_price');
            $table->decimal('selling_price', 12, 2)->default(0.00)->after('base_cost_price');
            $table->boolean('is_composite')->default(false)->after('is_huenics_owned');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['product_code', 'description', 'base_cost_price', 'selling_price', 'is_composite']);
        });
    }
};
