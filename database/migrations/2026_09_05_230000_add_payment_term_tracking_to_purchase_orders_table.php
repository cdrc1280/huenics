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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('payment_term_type', 50)->nullable()->after('payment_terms');
            $table->date('payment_due_date')->nullable()->after('payment_term_type');
            $table->string('payment_status', 50)->default('unpaid')->after('payment_due_date');
            $table->dateTime('paid_at')->nullable()->after('payment_status');
            $table->string('pdc_check_number', 100)->nullable()->after('paid_at');
            $table->string('pdc_bank', 150)->nullable()->after('pdc_check_number');
            $table->dateTime('last_payment_reminder_sent_at')->nullable()->after('pdc_bank');
            $table->string('payment_account', 150)->nullable()->after('last_payment_reminder_sent_at');
            $table->text('payment_notes')->nullable()->after('payment_account');

            $table->index(['payment_status', 'payment_due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex(['payment_status', 'payment_due_date']);
            $table->dropColumn([
                'payment_term_type',
                'payment_due_date',
                'payment_status',
                'paid_at',
                'pdc_check_number',
                'pdc_bank',
                'last_payment_reminder_sent_at',
                'payment_account',
                'payment_notes',
            ]);
        });
    }
};
