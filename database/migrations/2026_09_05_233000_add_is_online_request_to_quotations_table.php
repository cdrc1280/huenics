<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->boolean('is_online_request')->default(false)->after('status')->index();
            $table->string('customer_email')->nullable()->after('customer_name')->index();
            $table->string('client_ip', 45)->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropIndex(['is_online_request']);
            $table->dropIndex(['customer_email']);
            $table->dropColumn(['is_online_request', 'customer_email', 'client_ip']);
        });
    }
};
