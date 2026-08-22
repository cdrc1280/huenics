<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'description')) {
                $table->text('description')->nullable()->after('action');
            }
            if (!Schema::hasColumn('audit_logs', 'event')) {
                $table->string('event', 50)->default('custom')->after('action');
            }
            if (!Schema::hasColumn('audit_logs', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('audit_logs', 'properties')) {
                $table->json('properties')->nullable()->after('new_value');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn(['description', 'event', 'user_agent', 'properties']);
        });
    }
};
