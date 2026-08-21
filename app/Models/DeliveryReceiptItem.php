<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_receipt_id',
        'po_line_item_id',
        'product_id',
        'description',
        'qty_delivered',
        'unit',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'qty_delivered' => 'decimal:4',
        ];
    }

    public function deliveryReceipt(): BelongsTo
    {
        return $this->belongsTo(DeliveryReceipt::class);
    }

    public function poLineItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderLineItem::class, 'po_line_item_id');
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
