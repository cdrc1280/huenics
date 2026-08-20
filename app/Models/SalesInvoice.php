<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesInvoice extends Model
{
    use HasFactory;

    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'si_number',
        'purchase_order_id',
        'delivery_receipt_id',
        'customer_name',
        'billing_address',
        'invoice_date',
        'due_date',
        'subtotal',
        'vat_amount',
        'total_amount',
        'payment_status',
        'payment_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date'     => 'date',
            'payment_date' => 'date',
            'subtotal'     => 'decimal:2',
            'vat_amount'   => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function deliveryReceipt(): BelongsTo
    {
        return $this->belongsTo(DeliveryReceipt::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesInvoiceItem::class)->orderBy('id');
    }

    public static function generateNumber(): string
    {
        $prefix = 'SI-' . date('Y') . '-';
        $last   = static::where('si_number', 'like', $prefix . '%')
            ->latest()->value('si_number');

        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
