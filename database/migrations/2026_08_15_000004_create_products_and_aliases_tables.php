<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('canonical_name');
            $table->string('sku')->nullable()->unique();
            $table->string('category')->nullable();
            $table->string('unit_default')->default('pcs');
            $table->decimal('default_price', 12, 2)->nullable();
            $table->boolean('is_huenics_owned')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->text('alias_text');
            $table->string('normalized_alias');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['normalized_alias', 'vendor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_aliases');
        Schema::dropIfExists('products');
    }
};
