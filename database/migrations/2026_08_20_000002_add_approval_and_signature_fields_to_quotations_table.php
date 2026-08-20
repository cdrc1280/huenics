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
        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('quotations', 'approved_at')) {
                $table->dateTime('approved_at')->nullable();
            }
            if (!Schema::hasColumn('quotations', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('quotations', 'reviewed_at')) {
                $table->dateTime('reviewed_at')->nullable();
            }
            if (!Schema::hasColumn('quotations', 'is_official_po')) {
                $table->boolean('is_official_po')->default(false);
            }
            if (!Schema::hasColumn('quotations', 'customer_signature_name')) {
                $table->string('customer_signature_name')->nullable();
            }
            if (!Schema::hasColumn('quotations', 'customer_signed_at')) {
                $table->dateTime('customer_signed_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                if (Schema::hasColumn('quotations', 'approved_by')) {
                    $table->dropForeign(['approved_by']);
                }
                if (Schema::hasColumn('quotations', 'reviewed_by')) {
                    $table->dropForeign(['reviewed_by']);
                }
            }
            $cols = [];
            foreach ([
                'approved_by', 'approved_at', 'reviewed_by', 'reviewed_at',
                'is_official_po', 'customer_signature_name', 'customer_signed_at'
            ] as $col) {
                if (Schema::hasColumn('quotations', $col)) {
                    $cols[] = $col;
                }
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
