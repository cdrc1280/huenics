<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->string('document_type')->default('purchase_order'); // purchase_order, order_slip, vendors_agreement
            $table->string('document_number')->nullable()->index();
            $table->date('document_date')->nullable();
            $table->string('original_filename');
            $table->string('disk_path');
            $table->char('file_hash', 64)->unique();
            $table->string('status')->default('uploaded')->index(); // uploaded, processing, requires_review, verified, failed, rejected
            $table->decimal('extraction_confidence', 5, 2)->nullable();
            $table->text('failure_reason')->nullable();
            $table->longText('raw_extracted_text')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('document_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->integer('line_no')->default(1);
            $table->string('material_code')->nullable();
            $table->text('description');
            $table->decimal('qty', 12, 4)->default(0);
            $table->string('unit', 50)->nullable()->default('pcs');
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('printed_total', 12, 2)->nullable();
            $table->decimal('computed_total', 12, 2)->default(0);
            $table->boolean('total_mismatch')->default(false);
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->text('raw_line_text')->nullable();
            $table->timestamps();
        });

        Schema::create('document_totals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->unique()->constrained('documents')->cascadeOnDelete();
            $table->decimal('printed_subtotal', 12, 2)->nullable();
            $table->decimal('printed_vat', 12, 2)->nullable();
            $table->decimal('printed_total', 12, 2)->nullable();
            $table->decimal('computed_subtotal', 12, 2)->default(0);
            $table->decimal('computed_vat', 12, 2)->default(0);
            $table->decimal('computed_grand_total', 12, 2)->default(0);
            $table->decimal('negotiated_amount', 12, 2)->nullable();
            $table->boolean('subtotal_mismatch')->default(false);
            $table->boolean('vat_mismatch')->default(false);
            $table->boolean('total_mismatch')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_totals');
        Schema::dropIfExists('document_line_items');
        Schema::dropIfExists('documents');
    }
};
