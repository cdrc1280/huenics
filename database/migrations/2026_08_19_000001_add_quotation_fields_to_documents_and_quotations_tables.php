<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('document_number');
            $table->string('customer_company')->nullable()->after('customer_name');
            $table->string('project_name')->nullable()->after('customer_company');
            $table->string('project_location')->nullable()->after('project_name');
            $table->string('phone_no')->nullable()->after('project_location');
        });

        Schema::table('document_line_items', function (Blueprint $table) {
            $table->decimal('discounted_price', 12, 2)->nullable()->after('unit_price');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->string('customer_company')->nullable()->after('customer_name');
            $table->string('project_name')->nullable()->after('customer_company');
            $table->string('project_location')->nullable()->after('project_name');
            $table->string('phone_no')->nullable()->after('project_location');
        });

        Schema::table('quotation_line_items', function (Blueprint $table) {
            $table->string('item_code')->nullable()->after('line_no');
            $table->decimal('discounted_price', 12, 2)->nullable()->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_line_items', function (Blueprint $table) {
            $table->dropColumn(['item_code', 'discounted_price']);
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['customer_company', 'project_name', 'project_location', 'phone_no']);
        });

        Schema::table('document_line_items', function (Blueprint $table) {
            $table->dropColumn('discounted_price');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'customer_company', 'project_name', 'project_location', 'phone_no']);
        });
    }
};
