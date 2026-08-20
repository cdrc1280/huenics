<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_document_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('document_type'); // 'purchase_order', 'order_slip', 'vendors_agreement'
            $table->integer('layout_version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->string('header_identifier_regex')->nullable(); // regex to detect this layout from text
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['vendor_id', 'document_type', 'layout_version'], 'vdl_vendor_type_version_unique');
        });

        Schema::create('vendor_layout_field_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layout_id')->constrained('vendor_document_layouts')->cascadeOnDelete();
            $table->string('field_key'); // 'document_number', 'order_date', 'line_no', 'description', 'qty', 'unit', 'unit_price', 'printed_total', 'printed_subtotal', 'printed_vat', 'printed_total', 'negotiated_amount'
            $table->string('target_scope')->default('header'); // 'header', 'line_item', 'totals'
            $table->string('extraction_strategy'); // 'regex_header', 'column_position', 'keyword_offset', 'table_row_index'
            $table->string('regex_pattern', 1000)->nullable();
            $table->integer('column_start')->nullable();
            $table->integer('column_end')->nullable();
            $table->integer('row_offset')->nullable();
            $table->string('post_process')->default('none'); // 'none', 'strip_commas', 'parse_decimal', 'parse_int', 'trim', 'uppercase'
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_layout_field_mappings');
        Schema::dropIfExists('vendor_document_layouts');
    }
};
