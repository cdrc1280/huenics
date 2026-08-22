<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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
        'quotation_document_id',
        'purchase_order_document_id',
        'order_slip_document_id',
        'final_amount',
        'order_date',
        'delivery_date',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'final_amount' => 'decimal:2',
            'order_date' => 'date',
            'delivery_date' => 'date',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
