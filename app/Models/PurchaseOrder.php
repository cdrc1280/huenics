<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    // Delivery statuses
    public const DELIVERY_PENDING   = 'pending';
    public const DELIVERY_TRANSIT   = 'in_transit';
    public const DELIVERY_DELIVERED = 'delivered';
    public const DELIVERY_OVERDUE   = 'overdue';

    // Warranty periods
    public const WARRANTY_6_MONTHS = '6_months';
    public const WARRANTY_1_YEAR   = '1_year';
    public const WARRANTY_2_YEARS  = '2_years';

    // Warranty statuses
    public const WARRANTY_ACTIVE       = 'active';
    public const WARRANTY_EXPIRING     = 'expiring_soon';
    public const WARRANTY_EXPIRED      = 'expired';
    public const WARRANTY_NONE         = 'no_warranty';

    // PO statuses
    public const STATUS_PENDING   = 'pending_delivery';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    public static function getWarrantyPeriodOptions(): array
    {
        return [
            self::WARRANTY_1_YEAR   => '1 Year (1yr)',
            self::WARRANTY_2_YEARS  => '2 Years (2yrs)',
            self::WARRANTY_6_MONTHS => '6 Months',
        ];
    }


    public static function getWarrantyPeriodMonths(string $period): int
    {
        return match ($period) {
            self::WARRANTY_6_MONTHS => 6,
            self::WARRANTY_1_YEAR   => 12,
            self::WARRANTY_2_YEARS  => 24,
            default                 => 12,
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
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'order_date'             => 'date',
            'expected_delivery_date' => 'date',
            'actual_delivery_date'   => 'date',
            'warranty_start_date'    => 'date',
            'warranty_end_date'      => 'date',
            'has_warranty'           => 'boolean',
            'order_amount'           => 'decimal:2',
            'total_cost'             => 'decimal:2',
            'realized_profit'        => 'decimal:2',
            'printed_vat'            => 'decimal:2',
            'computed_vat'           => 'decimal:2',
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
            && $this->expected_delivery_date->isPast();
    }

    public function getDeliveryStatusLabelAttribute(): string
    {
        return match ($this->delivery_status) {
            self::DELIVERY_PENDING   => 'Pending',
            self::DELIVERY_TRANSIT   => 'In Transit',
            self::DELIVERY_DELIVERED => 'Delivered',
            self::DELIVERY_OVERDUE   => 'Overdue',
            default                  => ucfirst($this->delivery_status),
        };
    }

    public function getWarrantyStatusLabelAttribute(): string
    {
        return match ($this->warranty_status) {
            self::WARRANTY_ACTIVE   => 'Active',
            self::WARRANTY_EXPIRING => 'Expiring Soon',
            self::WARRANTY_EXPIRED  => 'Expired',
            self::WARRANTY_NONE     => 'No Warranty',
            default                 => 'Unknown',
        };
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    public static function generateNumber(): string
    {
        $prefix = 'PO-' . date('Y') . '-';
        $last   = static::where('po_number', 'like', $prefix . '%')
            ->latest()->value('po_number');

        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
