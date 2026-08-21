<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_line_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_order_line_items', 'item_code')) {
                $table->string('item_code', 100)->nullable()->after('line_no');
            }
            if (!Schema::hasColumn('purchase_order_line_items', 'discounted_price')) {
                $table->decimal('discounted_price', 12, 2)->nullable()->after('unit_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_line_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_order_line_items', 'item_code')) {
                $table->dropColumn('item_code');
            }
            if (Schema::hasColumn('purchase_order_line_items', 'discounted_price')) {
                $table->dropColumn('discounted_price');
            }
        });
    }
};
