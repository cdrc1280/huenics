<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * Get a configuration setting with caching and fallback default.
     */
    public static function getSetting(string $key, mixed $default = null): mixed
    {
        return Cache::remember("company_setting_{$key}", 3600, function () use ($key, $default) {
            $record = static::where('key', $key)->first();
            return $record?->value ?? $default;
        });
    }

    /**
     * Update or create a configuration setting and clear its cache.
     */
    public static function setSetting(string $key, mixed $value, ?string $description = null): self
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            array_filter([
                'value' => (string) $value,
                'description' => $description,
            ], fn ($v) => $v !== null)
        );

        Cache::forget("company_setting_{$key}");
        Cache::forget('company_years_in_business');

        return $setting;
    }

    /**
     * Dynamically compute the company's operational years in business.
     */
    public static function getYearsInBusiness(): int
    {
        return Cache::remember('company_years_in_business', 3600, function () {
            // Check if there is an explicit override setting
            $override = static::getSetting('years_in_business_override');
            if ($override !== null && is_numeric($override) && (int) $override > 0) {
                return (int) $override;
            }

            $foundingYear = (int) static::getSetting('founding_year', 2022);
            $currentYear = (int) date('Y');

            return max(1, $currentYear - $foundingYear);
        });
    }
}
