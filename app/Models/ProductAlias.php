<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductAlias extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\LogsActivity;

    protected $fillable = [
        'product_id',
        'alias_text',
        'normalized_alias',
        'vendor_id',
    ];

    protected static function booted(): void
    {
        static::saving(function ($alias) {
            if (empty($alias->normalized_alias)) {
                $alias->normalized_alias = self::normalize($alias->alias_text);
            }
        });
    }

    public static function normalize(string $text): string
    {
        // Lowercase, trim, collapse consecutive spaces and special characters
        $normalized = Str::lower(trim($text));
        $normalized = preg_replace('/[^\w\s\-\.\/]/u', '', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        return trim($normalized);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
