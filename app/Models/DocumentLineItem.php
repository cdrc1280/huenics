<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentLineItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'line_no',
        'material_code',
        'description',
        'qty',
        'unit',
        'unit_price',
        'discounted_price',
        'printed_total',
        'computed_total',
        'total_mismatch',
        'product_id',
        'raw_line_text',
    ];

    protected function casts(): array
    {
        return [
            'line_no' => 'integer',
            'qty' => 'decimal:4',
            'unit_price' => 'decimal:2',
            'discounted_price' => 'decimal:2',
            'printed_total' => 'decimal:2',
            'computed_total' => 'decimal:2',
            'total_mismatch' => 'boolean',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted(): void
    {
        static::saving(function ($item) {
            if (empty($item->description) && !empty($item->product_id)) {
                $item->description = $item->product?->canonical_name ?? Product::find($item->product_id)?->canonical_name ?? ('Product #' . $item->product_id);
            }
        });
    }
}
