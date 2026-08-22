<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity_on_hand', 12, 4)->default(0);
            $table->decimal('quantity_reserved', 12, 4)->default(0);
            $table->decimal('reorder_point', 12, 4)->nullable();
            $table->string('unit', 50)->default('pcs');
            $table->timestamp('last_counted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->string('transaction_type'); // initial_stock, purchase_in, order_out, adjustment_up, adjustment_down, reserved, unreserved
            $table->string('reference_type')->nullable(); // document, transaction, manual
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('quantity', 12, 4);
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventory_items');
    }
};
