<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number', 100)->unique();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('sales_agent_id')->constrained('users');
            $table->string('customer_name');
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('negotiated_amount', 12, 2)->nullable();
            $table->decimal('total_cost', 12, 2)->default(0.00);
            $table->decimal('estimated_profit', 12, 2)->default(0.00);
            $table->string('status')->default('pending'); // pending, approved, rejected, converted_to_po
            $table->text('rejection_reason')->nullable();
            $table->date('quotation_date');
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['sales_agent_id', 'status']);
            $table->index('quotation_date');
        });

        Schema::create('quotation_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->integer('line_no')->default(1);
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->text('description');
            $table->decimal('qty', 12, 4)->default(1);
            $table->string('unit', 50)->default('pcs');
            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->decimal('base_cost', 12, 2)->default(0.00);
            $table->decimal('line_total', 12, 2)->default(0.00);
            $table->decimal('gross_profit', 12, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_line_items');
        Schema::dropIfExists('quotations');
    }
};
