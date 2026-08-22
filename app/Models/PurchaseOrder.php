<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\WarrantyPeriod;
use App\Enums\WarrantyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * @property int $id
 * @property string $po_number
 * @property int|null $document_id
 * @property int|null $quotation_id
 * @property int|null $sales_agent_id
 * @property string|null $customer_name
 * @property int|null $project_id
 * @property float $order_amount
 * @property float $total_cost
 * @property float $realized_profit
 * @property float $printed_vat
 * @property float $computed_vat
 * @property \Illuminate\Support\Carbon|null $order_date
 * @property \Illuminate\Support\Carbon|null $expected_delivery_date
 * @property \Illuminate\Support\Carbon|null $actual_delivery_date
 * @property string|null $delivery_receipt_no
 * @property string $delivery_status
 * @property bool $has_warranty
 * @property string|null $warranty_period
 * @property \Illuminate\Support\Carbon|null $warranty_start_date
 * @property \Illuminate\Support\Carbon|null $warranty_end_date
 * @property string $warranty_status
 * @property string $status
 * @property bool $is_inventory_deducted
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\LogsActivity;

    // Delivery statuses
    public const DELIVERY_PENDING = DeliveryStatus::Pending->value;
    public const DELIVERY_TRANSIT = DeliveryStatus::InTransit->value;
    public const DELIVERY_DELIVERED = DeliveryStatus::Delivered->value;
    public const DELIVERY_OVERDUE = DeliveryStatus::Overdue->value;

    // Warranty periods (Strictly 2 options: 1 Year, 2 Years & 6 Months)
    public const WARRANTY_1_YEAR = WarrantyPeriod::OneYear->value;
    public const WARRANTY_2_YEARS_6_MONTHS = WarrantyPeriod::TwoYearsSixMonths->value;

    // Warranty statuses
    public const WARRANTY_ACTIVE = WarrantyStatus::Active->value;
    public const WARRANTY_EXPIRING = WarrantyStatus::ExpiringSoon->value;
    public const WARRANTY_EXPIRED = WarrantyStatus::Expired->value;
    public const WARRANTY_NONE = WarrantyStatus::NoWarranty->value;

    // PO statuses
    public const STATUS_PENDING = PurchaseOrderStatus::Pending->value;
    public const STATUS_APPROVED = PurchaseOrderStatus::Approved->value;
    public const STATUS_PENDING_DELIVERY = PurchaseOrderStatus::PendingDelivery->value;
    public const STATUS_DELIVERED = PurchaseOrderStatus::Delivered->value;
    public const STATUS_CANCELLED = PurchaseOrderStatus::Cancelled->value;
    public const STATUS_REJECTED = PurchaseOrderStatus::Rejected->value;

    public static function getWarrantyPeriodOptions(): array
    {
        return [
            self::WARRANTY_1_YEAR => '1 Year (1 yr)',
            self::WARRANTY_2_YEARS_6_MONTHS => '2 Years & 6 Months (2yrs and 6 months)',
        ];
    }

    public static function getWarrantyPeriodMonths(string $period): int
    {
        return match ($period) {
            self::WARRANTY_1_YEAR => 12,
            self::WARRANTY_2_YEARS_6_MONTHS, '2_years', '2yrs_6months' => 30,
            '6_months' => 6,
            default => 12,
        };
    }

    protected $fillable = [
        'po_number',
        'document_id',
        'quotation_id',
        'sales_agent_id',
        'customer_name',
        'project_id',
        'order_amount',
        'total_cost',
        'realized_profit',
        'printed_vat',
        'computed_vat',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'delivery_receipt_no',
        'delivery_status',
        'has_warranty',
        'warranty_period',
        'warranty_start_date',
        'warranty_end_date',
        'warranty_status',
        'status',
        'is_inventory_deducted',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'expected_delivery_date' => 'date',
            'actual_delivery_date' => 'date',
            'warranty_start_date' => 'date',
            'warranty_end_date' => 'date',
            'has_warranty' => 'boolean',
            'is_inventory_deducted' => 'boolean',
            'order_amount' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'realized_profit' => 'decimal:2',
            'printed_vat' => 'decimal:2',
            'computed_vat' => 'decimal:2',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function salesAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_agent_id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
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
        return $this->hasMany(PurchaseOrderLineItem::class)->orderBy('line_no');
    }

    public function deliveryReceipts(): HasMany
    {
        return $this->hasMany(DeliveryReceipt::class);
    }

    public function salesInvoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class);
    }

    // ─── Computed Attributes ──────────────────────────────────────────

    public function getWarrantyPeriodLabelAttribute(): string
    {
        return self::getWarrantyPeriodOptions()[$this->warranty_period] ?? '1 Year';
    }

    public function getWarrantyMonthsRemainingAttribute(): int
    {
        if (!$this->warranty_end_date || $this->warranty_status === self::WARRANTY_EXPIRED) {
            return 0;
        }
        return (int) now()->diffInMonths($this->warranty_end_date, false);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->delivery_status === self::DELIVERY_PENDING
            && $this->expected_delivery_date
            && \Carbon\Carbon::parse($this->expected_delivery_date)->isPast();
    }

    public function getDeliveryStatusLabelAttribute(): string
    {
        return match ($this->delivery_status) {
            self::DELIVERY_PENDING => 'Pending',
            self::DELIVERY_TRANSIT => 'In Transit',
            self::DELIVERY_DELIVERED => 'Delivered',
            self::DELIVERY_OVERDUE => 'Overdue',
            default => ucfirst($this->delivery_status),
        };
    }

    public function getWarrantyStatusLabelAttribute(): string
    {
        return match ($this->warranty_status) {
            self::WARRANTY_ACTIVE => 'Active',
            self::WARRANTY_EXPIRING => 'Expiring Soon',
            self::WARRANTY_EXPIRED => 'Expired',
            self::WARRANTY_NONE => 'No Warranty',
            default => 'Unknown',
        };
    }

    public function isReviewed(): bool
    {
        return $this->isApproved()
            || in_array($this->status, [
                self::STATUS_APPROVED,
                self::STATUS_PENDING_DELIVERY,
                self::STATUS_DELIVERED,
                'reviewed',
                'approved',
                'delivered',
            ], true);
    }

    public function isApproved(): bool
    {
        return in_array($this->status, [
            self::STATUS_APPROVED,
            self::STATUS_PENDING_DELIVERY,
            self::STATUS_DELIVERED,
            'approved',
            'pending_delivery',
            'delivered',
        ], true);
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    public static function generateNumber(): string
    {
        $prefix = 'PO-' . date('Y') . '-';
        $numbers = static::where('po_number', 'like', $prefix . '%')->pluck('po_number');

        $maxSeq = 0;
        foreach ($numbers as $num) {
            $val = (int) substr($num, strlen($prefix));
            if ($val > $maxSeq) {
                $maxSeq = $val;
            }
        }

        $nextSeq = $maxSeq + 1;
        $candidate = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

        while (static::where('po_number', $candidate)->exists()) {
            $nextSeq++;
            $candidate = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }
}
