<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderLineItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'line_no',
        'item_code',
        'product_id',
        'description',
        'qty',
        'unit',
        'unit_price',
        'discounted_price',
        'base_cost',
        'line_total',
        'line_cost',
    ];

    protected function casts(): array
    {
        return [
            'qty'              => 'decimal:4',
            'unit_price'       => 'decimal:2',
            'discounted_price' => 'decimal:2',
            'base_cost'        => 'decimal:2',
            'line_total'       => 'decimal:2',
            'line_cost'        => 'decimal:2',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function selectedComponents(): HasMany
    {
        return $this->hasMany(PoItemSelectedComponent::class, 'po_line_item_id');
    }

    protected static function booted(): void
    {
        static::saving(function ($item) {
            if (empty($item->description) && !empty($item->product_id)) {
                $item->description = $item->product?->canonical_name ?? Product::find($item->product_id)?->canonical_name ?? ('Product #' . $item->product_id);
            }
        });
    }

    public function recompute(): void
    {
        $this->line_total = round((float) $this->qty * (float) $this->unit_price, 2);
        $this->line_cost  = round((float) $this->qty * (float) $this->base_cost, 2);
    }
}
