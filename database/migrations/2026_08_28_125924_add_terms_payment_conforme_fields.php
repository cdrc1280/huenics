<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Quotations: terms & conditions + payment terms
        Schema::table('quotations', function (Blueprint $table) {
            $table->text('terms_and_conditions')->nullable()->after('notes');
            $table->string('payment_terms', 255)->nullable()->after('terms_and_conditions');
            $table->string('delivery_terms', 255)->nullable()->after('payment_terms');
        });

        // Purchase Orders: terms & conditions + payment terms + conforme PO flag
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->text('terms_and_conditions')->nullable()->after('notes');
            $table->string('payment_terms', 255)->nullable()->after('terms_and_conditions');
            $table->string('delivery_terms', 255)->nullable()->after('payment_terms');
            $table->boolean('is_conforme_po')->default(false)->after('is_completed');
        });

        // Documents: payment + delivery terms extracted from OCR
        Schema::table('documents', function (Blueprint $table) {
            $table->string('payment_terms', 255)->nullable()->after('phone_no');
            $table->string('delivery_terms', 255)->nullable()->after('payment_terms');
            $table->text('terms_and_conditions')->nullable()->after('delivery_terms');
        });

        // Warranty period data migration: convert '2_years_6_months' to '2_years'
        \Illuminate\Support\Facades\DB::table('purchase_orders')
            ->where('warranty_period', '2_years_6_months')
            ->update(['warranty_period' => '2_years']);
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['terms_and_conditions', 'payment_terms', 'delivery_terms']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['terms_and_conditions', 'payment_terms', 'delivery_terms', 'is_conforme_po']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['payment_terms', 'delivery_terms', 'terms_and_conditions']);
        });

        // Revert warranty period data
        \Illuminate\Support\Facades\DB::table('purchase_orders')
            ->where('warranty_period', '2_years')
            ->update(['warranty_period' => '2_years_6_months']);
    }
};
