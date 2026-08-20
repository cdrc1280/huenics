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
        'product_id',
        'description',
        'qty',
        'unit',
        'unit_price',
        'base_cost',
        'line_total',
        'line_cost',
    ];

    protected function casts(): array
    {
        return [
            'qty'        => 'decimal:4',
            'unit_price' => 'decimal:2',
            'base_cost'  => 'decimal:2',
            'line_total' => 'decimal:2',
            'line_cost'  => 'decimal:2',
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

    public function recompute(): void
    {
        $this->line_total = round((float) $this->qty * (float) $this->unit_price, 2);
        $this->line_cost  = round((float) $this->qty * (float) $this->base_cost, 2);
    }
}
