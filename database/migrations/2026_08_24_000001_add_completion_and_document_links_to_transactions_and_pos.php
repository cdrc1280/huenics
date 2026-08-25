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
        // 1. Delivery Receipts
        if (Schema::hasTable('delivery_receipts')) {
            Schema::table('delivery_receipts', function (Blueprint $table) {
                if (!Schema::hasColumn('delivery_receipts', 'document_id')) {
                    $table->foreignId('document_id')->nullable()->after('purchase_order_id')->constrained('documents')->nullOnDelete();
                }
            });
        }

        // 2. Sales Invoices
        if (Schema::hasTable('sales_invoices')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('sales_invoices', 'document_id')) {
                    $table->foreignId('document_id')->nullable()->after('delivery_receipt_id')->constrained('documents')->nullOnDelete();
                }
            });
        }

        // 3. Transactions
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('transactions', 'purchase_order_id')) {
                    $table->foreignId('purchase_order_id')->nullable()->after('vendor_id')->constrained('purchase_orders')->nullOnDelete();
                }
                if (!Schema::hasColumn('transactions', 'delivery_receipt_document_id')) {
                    $table->foreignId('delivery_receipt_document_id')->nullable()->after('order_slip_document_id')->constrained('documents')->nullOnDelete();
                }
                if (!Schema::hasColumn('transactions', 'sales_invoice_document_id')) {
                    $table->foreignId('sales_invoice_document_id')->nullable()->after('delivery_receipt_document_id')->constrained('documents')->nullOnDelete();
                }
                if (!Schema::hasColumn('transactions', 'is_completed')) {
                    $table->boolean('is_completed')->default(false)->after('status');
                }
            });
        }

        // 4. Purchase Orders
        if (Schema::hasTable('purchase_orders')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_orders', 'is_completed')) {
                    $table->boolean('is_completed')->default(false)->after('is_inventory_deducted');
                }
                if (!Schema::hasColumn('purchase_orders', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable()->after('is_completed');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('purchase_orders')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_orders', 'completed_at')) {
                    $table->dropColumn('completed_at');
                }
                if (Schema::hasColumn('purchase_orders', 'is_completed')) {
                    $table->dropColumn('is_completed');
                }
            });
        }

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (Schema::hasColumn('transactions', 'is_completed')) {
                    $table->dropColumn('is_completed');
                }
                if (Schema::hasColumn('transactions', 'sales_invoice_document_id')) {
                    $table->dropForeign(['sales_invoice_document_id']);
                    $table->dropColumn('sales_invoice_document_id');
                }
                if (Schema::hasColumn('transactions', 'delivery_receipt_document_id')) {
                    $table->dropForeign(['delivery_receipt_document_id']);
                    $table->dropColumn('delivery_receipt_document_id');
                }
                if (Schema::hasColumn('transactions', 'purchase_order_id')) {
                    $table->dropForeign(['purchase_order_id']);
                    $table->dropColumn('purchase_order_id');
                }
            });
        }

        if (Schema::hasTable('sales_invoices')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                if (Schema::hasColumn('sales_invoices', 'document_id')) {
                    $table->dropForeign(['document_id']);
                    $table->dropColumn('document_id');
                }
            });
        }

        if (Schema::hasTable('delivery_receipts')) {
            Schema::table('delivery_receipts', function (Blueprint $table) {
                if (Schema::hasColumn('delivery_receipts', 'document_id')) {
                    $table->dropForeign(['document_id']);
                    $table->dropColumn('document_id');
                }
            });
        }
    }
};
