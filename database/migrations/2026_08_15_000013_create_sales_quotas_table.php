<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('month'); // 1-12
            $table->unsignedSmallInteger('year');
            $table->decimal('target_amount', 12, 2)->default(0.00);
            $table->decimal('achieved_amount', 12, 2)->default(0.00);
            $table->unsignedInteger('total_quotations')->default(0);
            $table->unsignedInteger('converted_pos')->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['user_id', 'month', 'year']);
            $table->index(['month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_quotas');
    }
};
