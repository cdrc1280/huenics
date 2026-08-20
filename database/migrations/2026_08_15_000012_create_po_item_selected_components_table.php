<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('po_item_selected_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_line_item_id')->constrained('purchase_order_line_items')->cascadeOnDelete();
            $table->foreignId('component_id')->nullable()->constrained('product_components')->nullOnDelete();
            $table->string('component_group', 100);
            $table->string('selected_option_name', 100);
            $table->decimal('unit_cost', 12, 2)->default(0.00);
            $table->decimal('total_cost', 12, 2)->default(0.00);
            $table->boolean('is_deducted_from_inventory')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('po_item_selected_components');
    }
};
