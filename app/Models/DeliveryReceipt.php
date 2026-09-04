<?php

namespace App\Models;

use App\Enums\DeliveryReceiptStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $dr_number
 * @property int $purchase_order_id
 * @property int|null $document_id
 * @property string|null $delivered_by
 * @property string|null $received_by
 * @property \Illuminate\Support\Carbon $delivery_date
 * @property string|null $remarks
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class DeliveryReceipt extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\LogsActivity;

    public const STATUS_DRAFT = DeliveryReceiptStatus::Draft->value;
    public const STATUS_DELIVERED = DeliveryReceiptStatus::Delivered->value;
    public const STATUS_CANCELLED = DeliveryReceiptStatus::Cancelled->value;

    protected $fillable = [
        'dr_number',
        'purchase_order_id',
        'customer_name',
        'customer_tin',
        'delivery_address',
        'terms',
        'project_name',
        'sales_invoice_numbers',
        'rs_number',
        'delivery_type',
        'document_id',
        'delivered_by',
        'received_by',
        'prepared_by',
        'approved_by',
        'delivery_date',
        'received_date',
        'remarks',
        'status',
        'file_path',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'received_date' => 'date',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
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
