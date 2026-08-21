<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_invoice_id',
        'po_line_item_id',
        'product_id',
        'description',
        'qty',
        'unit',
        'unit_price',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'qty'        => 'decimal:4',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
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
