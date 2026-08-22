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
        if (!Schema::hasTable('delivery_receipts')) {
            Schema::create('delivery_receipts', function (Blueprint $table) {
                $table->id();
                $table->string('dr_number')->unique();
                $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
                $table->string('delivered_by')->nullable();
                $table->string('received_by')->nullable();
                $table->date('delivery_date');
                $table->text('remarks')->nullable();
                $table->string('status')->default('draft');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('delivery_receipt_items')) {
            Schema::create('delivery_receipt_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('delivery_receipt_id')->constrained()->cascadeOnDelete();
                $table->foreignId('po_line_item_id')->nullable()->constrained('purchase_order_line_items')->nullOnDelete();
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
                $table->string('description');
                $table->decimal('qty_delivered', 12, 4);
                $table->string('unit')->default('pcs');
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sales_invoices')) {
            Schema::create('sales_invoices', function (Blueprint $table) {
                $table->id();
                $table->string('si_number')->unique();
                $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('delivery_receipt_id')->nullable()->constrained()->nullOnDelete();
                $table->string('customer_name');
                $table->text('billing_address')->nullable();
                $table->date('invoice_date');
                $table->date('due_date')->nullable();
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('vat_amount', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->string('payment_status')->default('unpaid');
                $table->date('payment_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('sales_invoice_items')) {
            Schema::create('sales_invoice_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sales_invoice_id')->constrained()->cascadeOnDelete();
                $table->foreignId('po_line_item_id')->nullable()->constrained('purchase_order_line_items')->nullOnDelete();
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
                $table->string('description');
                $table->decimal('qty', 12, 4);
                $table->string('unit')->default('pcs');
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('line_total', 12, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoice_items');
        Schema::dropIfExists('sales_invoices');
        Schema::dropIfExists('delivery_receipt_items');
        Schema::dropIfExists('delivery_receipts');
    }
};
