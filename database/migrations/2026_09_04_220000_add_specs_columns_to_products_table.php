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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'wattage')) {
                $table->string('wattage')->nullable()->after('category');
            }
            if (!Schema::hasColumn('products', 'voltage')) {
                $table->string('voltage')->nullable()->after('wattage');
            }
            if (!Schema::hasColumn('products', 'color_temperature')) {
                $table->string('color_temperature')->nullable()->after('voltage');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['wattage', 'voltage', 'color_temperature']);
        });
    }
};
