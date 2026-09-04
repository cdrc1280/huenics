<?php

namespace App\Models;

use App\Enums\SalesInvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $si_number
 * @property int $purchase_order_id
 * @property int|null $delivery_receipt_id
 * @property int|null $document_id
 * @property string $customer_name
 * @property string|null $billing_address
 * @property \Illuminate\Support\Carbon $invoice_date
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property float $subtotal
 * @property float $vat_amount
 * @property float $total_amount
 * @property string $payment_status
 * @property \Illuminate\Support\Carbon|null $payment_date
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class SalesInvoice extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\LogsActivity;

    public const STATUS_UNPAID = SalesInvoiceStatus::Unpaid->value;
    public const STATUS_PARTIAL = SalesInvoiceStatus::Partial->value;
    public const STATUS_PAID = SalesInvoiceStatus::Paid->value;
    public const STATUS_CANCELLED = SalesInvoiceStatus::Cancelled->value;

    protected $fillable = [
        'si_number',
        'purchase_order_id',
        'delivery_receipt_id',
        'delivery_receipt_numbers',
        'collection_receipt_numbers',
        'rs_number',
        'document_id',
        'customer_name',
        'customer_tin',
        'business_style',
        'billing_address',
        'terms',
        'osca_pwd_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'discount_amount',
        'net_of_vat',
        'vatable_sales',
        'vat_exempt_sales',
        'zero_rated_sales',
        'vat_amount',
        'total_amount',
        'withholding_tax',
        'payment_status',
        'payment_date',
        'cashier_representative',
        'cashier_signature_date',
        'notes',
        'file_path',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date'           => 'date',
            'due_date'               => 'date',
            'payment_date'           => 'date',
            'cashier_signature_date' => 'date',
            'subtotal'               => 'decimal:2',
            'discount_amount'        => 'decimal:2',
            'net_of_vat'             => 'decimal:2',
            'vatable_sales'          => 'decimal:2',
            'vat_exempt_sales'       => 'decimal:2',
            'zero_rated_sales'       => 'decimal:2',
            'vat_amount'             => 'decimal:2',
            'total_amount'           => 'decimal:2',
            'withholding_tax'        => 'decimal:2',
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

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
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
