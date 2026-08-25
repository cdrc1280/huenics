<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $transaction_code
 * @property int|null $project_id
 * @property int|null $vendor_id
 * @property int|null $purchase_order_id
 * @property int|null $quotation_document_id
 * @property int|null $purchase_order_document_id
 * @property int|null $order_slip_document_id
 * @property int|null $delivery_receipt_document_id
 * @property int|null $sales_invoice_document_id
 * @property float $final_amount
 * @property \Illuminate\Support\Carbon|null $order_date
 * @property \Illuminate\Support\Carbon|null $delivery_date
 * @property string $status
 * @property bool $is_completed
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Transaction extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\LogsActivity;

    public const STATUS_PENDING   = TransactionStatus::PendingDelivery->value;
    public const STATUS_DELIVERED = TransactionStatus::Delivered->value;
    public const STATUS_CANCELLED = TransactionStatus::Cancelled->value;

    protected $fillable = [
        'transaction_code',
        'project_id',
        'vendor_id',
        'purchase_order_id',
        'quotation_document_id',
        'purchase_order_document_id',
        'order_slip_document_id',
        'delivery_receipt_document_id',
        'sales_invoice_document_id',
        'final_amount',
        'order_date',
        'delivery_date',
        'status',
        'is_completed',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'final_amount' => 'decimal:2',
            'order_date' => 'date',
            'delivery_date' => 'date',
            'is_completed' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($trx) {
            if (empty($trx->transaction_code)) {
                $dateStr = now()->format('Ymd');
                $random = strtoupper(Str::random(4));
                $trx->transaction_code = "TRX-{$dateStr}-{$random}";
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function quotationDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'quotation_document_id');
    }

    public function purchaseOrderDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'purchase_order_document_id');
    }

    public function orderSlipDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'order_slip_document_id');
    }

    public function deliveryReceiptDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'delivery_receipt_document_id');
    }

    public function salesInvoiceDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'sales_invoice_document_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Checks if all required lifecycle documents are attached (Quotation + PO + DR + SI).
     */
    public function hasFullLifecycleDocuments(): bool
    {
        return !empty($this->quotation_document_id)
            && !empty($this->purchase_order_document_id)
            && !empty($this->delivery_receipt_document_id)
            && !empty($this->sales_invoice_document_id);
    }

    /**
     * Checks if DR & SI fulfillment documents are attached.
     */
    public function hasFulfillmentDocuments(): bool
    {
        return !empty($this->delivery_receipt_document_id)
            && !empty($this->sales_invoice_document_id);
    }
}
