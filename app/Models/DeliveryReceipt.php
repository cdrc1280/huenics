<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryReceipt extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'dr_number',
        'purchase_order_id',
        'delivered_by',
        'received_by',
        'delivery_date',
        'remarks',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryReceiptItem::class)->orderBy('id');
    }

    public static function generateNumber(): string
    {
        $prefix = 'DR-' . date('Y') . '-';
        $last   = static::where('dr_number', 'like', $prefix . '%')
            ->latest()->value('dr_number');

        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
