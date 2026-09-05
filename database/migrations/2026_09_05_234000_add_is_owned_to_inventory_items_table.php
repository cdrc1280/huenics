<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_items', 'is_owned')) {
                $table->boolean('is_owned')->default(true)->after('unit')->index();
            }
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_transactions', 'is_owned')) {
                $table->boolean('is_owned')->default(true)->after('quantity')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_transactions', 'is_owned')) {
                $table->dropIndex(['is_owned']);
                $table->dropColumn(['is_owned']);
            }
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_items', 'is_owned')) {
                $table->dropIndex(['is_owned']);
                $table->dropColumn(['is_owned']);
            }
        });
    }
};
