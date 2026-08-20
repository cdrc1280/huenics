<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->foreignId('quotation_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('purchase_order_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('order_slip_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->decimal('final_amount', 12, 2)->default(0);
            $table->date('order_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('status')->default('pending_delivery'); // pending_delivery, delivered, cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // document_verified, line_item_edited, alias_created, document_rejected, layout_updated
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('transactions');
    }
};
