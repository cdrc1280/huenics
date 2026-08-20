<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_product_id')->constrained('products')->cascadeOnDelete();
            $table->string('component_group', 100); // e.g. 'LED COB', 'LED Driver', 'Heatsink'
            $table->string('option_name', 100);      // e.g. '3000k', '700ma', 'With Heatsink'
            $table->foreignId('component_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->decimal('additional_cost', 12, 2)->default(0.00);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['parent_product_id', 'component_group']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_components');
    }
};
