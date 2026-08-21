<?php

namespace App\Models;

use App\Enums\QuotationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quotation extends Model
{
    use HasFactory;

    public const STATUS_PENDING      = QuotationStatus::Pending->value;
    public const STATUS_APPROVED     = QuotationStatus::Approved->value;
    public const STATUS_REJECTED     = QuotationStatus::Rejected->value;
    public const STATUS_CONVERTED    = QuotationStatus::ConvertedToPo->value;

    protected $fillable = [
        'quotation_number',
        'document_id',
        'sales_agent_id',
        'customer_name',
        'customer_company',
        'project_id',
        'project_name',
        'project_location',
        'phone_no',
        'total_amount',
        'negotiated_amount',
        'total_cost',
        'estimated_profit',
        'status',
        'rejection_reason',
        'quotation_date',
        'valid_until',
        'notes',
        'approved_by',
        'approved_at',
        'reviewed_by',
        'reviewed_at',
        'is_official_po',
        'customer_signature_name',
        'customer_signed_at',
    ];

    protected function casts(): array
    {
        return [
            'quotation_date' => 'date',
            'valid_until'    => 'date',
            'approved_at'    => 'datetime',
            'reviewed_at'    => 'datetime',
            'customer_signed_at' => 'datetime',
            'is_official_po' => 'boolean',
            'total_amount'   => 'decimal:2',
            'negotiated_amount' => 'decimal:2',
            'total_cost'     => 'decimal:2',
            'estimated_profit' => 'decimal:2',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function salesAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_agent_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isReviewed(): bool
    {
        return $this->reviewed_by !== null;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isReadyForConversion(): bool
    {
        return $this->status === self::STATUS_APPROVED && $this->reviewed_by !== null;
    }

    public function canServeAsOfficialPO(): bool
    {
        return (bool) $this->is_official_po && !empty($this->customer_signature_name);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(QuotationLineItem::class)->orderBy('line_no');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeByAgent($query, int $userId)
    {
        return $query->where('sales_agent_id', $userId);
    }

    public function scopeWonThisMonth($query)
    {
        return $query->where('status', self::STATUS_CONVERTED)
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year);
    }

    // ─── Computed Attributes ──────────────────────────────────────────

    public function getIsExpiredAttribute(): bool
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING   => 'Pending',
            self::STATUS_APPROVED  => 'Approved',
            self::STATUS_REJECTED  => 'Rejected / Lost',
            self::STATUS_CONVERTED => 'Converted to PO',
            default                => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING   => 'warning',
            self::STATUS_APPROVED  => 'info',
            self::STATUS_REJECTED  => 'danger',
            self::STATUS_CONVERTED => 'success',
            default                => 'gray',
        };
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    public static function generateNumber(): string
    {
        $prefix = 'QT-' . date('Y') . '-';
        $last   = static::where('quotation_number', 'like', $prefix . '%')
            ->latest()->value('quotation_number');

        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
