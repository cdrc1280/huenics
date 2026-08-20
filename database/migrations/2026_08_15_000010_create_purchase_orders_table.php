<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number', 100)->unique();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
            $table->foreignId('sales_agent_id')->constrained('users');
            $table->string('customer_name');
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->decimal('order_amount', 12, 2)->default(0.00);
            $table->decimal('total_cost', 12, 2)->default(0.00);
            $table->decimal('realized_profit', 12, 2)->default(0.00);
            $table->decimal('printed_vat', 12, 2)->nullable();
            $table->decimal('computed_vat', 12, 2)->nullable();
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->string('delivery_receipt_no', 100)->nullable();
            $table->string('delivery_status')->default('pending'); // pending, in_transit, delivered, overdue
            $table->boolean('has_warranty')->default(true);
            // Fixed warranty periods: 6_months, 1_year, 2_years
            $table->string('warranty_period')->default('1_year'); // 6_months | 1_year | 2_years
            $table->date('warranty_start_date')->nullable();
            $table->date('warranty_end_date')->nullable();
            $table->string('warranty_status')->default('no_warranty'); // active, expiring_soon, expired, no_warranty
            $table->string('status')->default('pending_delivery'); // pending_delivery, delivered, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['sales_agent_id', 'status']);
            $table->index('delivery_status');
            $table->index('warranty_status');
        });

        Schema::create('purchase_order_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->integer('line_no')->default(1);
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->text('description');
            $table->decimal('qty', 12, 4)->default(1);
            $table->string('unit', 50)->default('pcs');
            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->decimal('base_cost', 12, 2)->default(0.00);
            $table->decimal('line_total', 12, 2)->default(0.00);
            $table->decimal('line_cost', 12, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_line_items');
        Schema::dropIfExists('purchase_orders');
    }
};
