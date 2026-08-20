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
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'original_mime_type')) {
                $table->string('original_mime_type')->nullable();
            }
            if (!Schema::hasColumn('documents', 'companion_pdf_path')) {
                $table->string('companion_pdf_path')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('documents', 'original_mime_type')) {
                $cols[] = 'original_mime_type';
            }
            if (Schema::hasColumn('documents', 'companion_pdf_path')) {
                $cols[] = 'companion_pdf_path';
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
