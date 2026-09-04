<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->index();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed initial default values for Huenics Industrial Sales Inc.
        DB::table('company_settings')->insert([
            [
                'key' => 'founding_year',
                'value' => '2022',
                'description' => 'Year the company was founded (used to dynamically compute years in business)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'years_in_business_override',
                'value' => null,
                'description' => 'Optional manual override for years in business (if set, overrides founding_year calculation)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'company_name',
                'value' => 'Huenics Industrial Sales Inc.',
                'description' => 'Official corporate entity name',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'company_tagline',
                'value' => 'Direct Importer & Wholesale Industrial Supply',
                'description' => 'Corporate tagline for public portal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'contact_phone',
                'value' => '0906-144-2553',
                'description' => 'Primary corporate contact number',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'contact_email',
                'value' => 'sales@huenics.com',
                'description' => 'Primary sales email address',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'office_address',
                'value' => '2F Starmall EDSA-Shaw, Mandaluyong City',
                'description' => 'Official business office address',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
