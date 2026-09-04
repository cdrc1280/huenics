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
        Schema::table('delivery_receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_receipts', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('purchase_order_id');
            }
            if (!Schema::hasColumn('delivery_receipts', 'customer_tin')) {
                $table->string('customer_tin')->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('delivery_receipts', 'delivery_address')) {
                $table->text('delivery_address')->nullable()->after('customer_tin');
            }
            if (!Schema::hasColumn('delivery_receipts', 'terms')) {
                $table->string('terms')->nullable()->after('delivery_address');
            }
            if (!Schema::hasColumn('delivery_receipts', 'project_name')) {
                $table->string('project_name')->nullable()->after('terms');
            }
            if (!Schema::hasColumn('delivery_receipts', 'sales_invoice_numbers')) {
                $table->string('sales_invoice_numbers')->nullable()->after('project_name');
            }
            if (!Schema::hasColumn('delivery_receipts', 'rs_number')) {
                $table->string('rs_number')->nullable()->after('sales_invoice_numbers');
            }
            if (!Schema::hasColumn('delivery_receipts', 'delivery_type')) {
                $table->string('delivery_type')->default('complete')->after('rs_number');
            }
            if (!Schema::hasColumn('delivery_receipts', 'prepared_by')) {
                $table->string('prepared_by')->nullable()->after('received_by');
            }
            if (!Schema::hasColumn('delivery_receipts', 'approved_by')) {
                $table->string('approved_by')->nullable()->after('prepared_by');
            }
            if (!Schema::hasColumn('delivery_receipts', 'received_date')) {
                $table->date('received_date')->nullable()->after('delivery_date');
            }
            if (!Schema::hasColumn('delivery_receipts', 'file_path')) {
                $table->string('file_path')->nullable()->after('status');
            }
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_invoices', 'customer_tin')) {
                $table->string('customer_tin')->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('sales_invoices', 'business_style')) {
                $table->string('business_style')->nullable()->after('customer_tin');
            }
            if (!Schema::hasColumn('sales_invoices', 'terms')) {
                $table->string('terms')->nullable()->after('business_style');
            }
            if (!Schema::hasColumn('sales_invoices', 'osca_pwd_id')) {
                $table->string('osca_pwd_id')->nullable()->after('terms');
            }
            if (!Schema::hasColumn('sales_invoices', 'delivery_receipt_numbers')) {
                $table->string('delivery_receipt_numbers')->nullable()->after('delivery_receipt_id');
            }
            if (!Schema::hasColumn('sales_invoices', 'collection_receipt_numbers')) {
                $table->string('collection_receipt_numbers')->nullable()->after('delivery_receipt_numbers');
            }
            if (!Schema::hasColumn('sales_invoices', 'rs_number')) {
                $table->string('rs_number')->nullable()->after('collection_receipt_numbers');
            }
            if (!Schema::hasColumn('sales_invoices', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('subtotal');
            }
            if (!Schema::hasColumn('sales_invoices', 'net_of_vat')) {
                $table->decimal('net_of_vat', 12, 2)->default(0)->after('discount_amount');
            }
            if (!Schema::hasColumn('sales_invoices', 'vatable_sales')) {
                $table->decimal('vatable_sales', 12, 2)->default(0)->after('net_of_vat');
            }
            if (!Schema::hasColumn('sales_invoices', 'vat_exempt_sales')) {
                $table->decimal('vat_exempt_sales', 12, 2)->default(0)->after('vatable_sales');
            }
            if (!Schema::hasColumn('sales_invoices', 'zero_rated_sales')) {
                $table->decimal('zero_rated_sales', 12, 2)->default(0)->after('vat_exempt_sales');
            }
            if (!Schema::hasColumn('sales_invoices', 'withholding_tax')) {
                $table->decimal('withholding_tax', 12, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('sales_invoices', 'cashier_representative')) {
                $table->string('cashier_representative')->nullable()->after('payment_date');
            }
            if (!Schema::hasColumn('sales_invoices', 'cashier_signature_date')) {
                $table->date('cashier_signature_date')->nullable()->after('cashier_representative');
            }
            if (!Schema::hasColumn('sales_invoices', 'file_path')) {
                $table->string('file_path')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_receipts', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name',
                'customer_tin',
                'delivery_address',
                'terms',
                'project_name',
                'sales_invoice_numbers',
                'rs_number',
                'delivery_type',
                'prepared_by',
                'approved_by',
                'received_date',
                'file_path',
            ]);
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropColumn([
                'customer_tin',
                'business_style',
                'terms',
                'osca_pwd_id',
                'delivery_receipt_numbers',
                'collection_receipt_numbers',
                'rs_number',
                'discount_amount',
                'net_of_vat',
                'vatable_sales',
                'vat_exempt_sales',
                'zero_rated_sales',
                'withholding_tax',
                'cashier_representative',
                'cashier_signature_date',
                'file_path',
            ]);
        });
    }
};
