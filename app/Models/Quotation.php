<?php

namespace App\Models;

use App\Enums\QuotationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\LogsActivity;

    public const STATUS_PENDING      = QuotationStatus::Pending->value;
    public const STATUS_REVIEWED     = QuotationStatus::Reviewed->value;
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
        return $this->reviewed_by !== null
            || in_array($this->status, [self::STATUS_REVIEWED, 'reviewed', self::STATUS_APPROVED, 'approved', self::STATUS_CONVERTED, 'converted_to_po'], true)
            || !empty($this->approved_by);
    }

    public function isApproved(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, 'approved'], true)
            || !empty($this->approved_by);
    }

    public function isRejected(): bool
    {
        return in_array($this->status, [self::STATUS_REJECTED, 'rejected'], true);
    }

    public function isConverted(): bool
    {
        return in_array($this->status, [self::STATUS_CONVERTED, 'converted_to_po'], true);
    }

    public function isReadyForConversion(): bool
    {
        if ($this->isConverted() || $this->isRejected()) {
            return false;
        }

        return $this->isApproved() || $this->canServeAsOfficialPO();
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

    public function purchaseOrder(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class, 'quotation_id');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'quotation_id');
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
            self::STATUS_REVIEWED  => 'Reviewed',
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
            self::STATUS_REVIEWED  => 'info',
            self::STATUS_APPROVED  => 'success',
            self::STATUS_REJECTED  => 'danger',
            self::STATUS_CONVERTED => 'primary',
            default                => 'gray',
        };
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    public static function generateNumber(): string
    {
        $prefix = 'QT-' . date('Y') . '-';
        $numbers = static::where('quotation_number', 'like', $prefix . '%')->pluck('quotation_number');

        $maxSeq = 0;
        foreach ($numbers as $num) {
            $val = (int) substr($num, strlen($prefix));
            if ($val > $maxSeq) {
                $maxSeq = $val;
            }
        }

        $nextSeq = $maxSeq + 1;
        $candidate = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

        while (static::where('quotation_number', $candidate)->exists()) {
            $nextSeq++;
            $candidate = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }
}
