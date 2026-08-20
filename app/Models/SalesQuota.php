<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesQuota extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'month',
        'year',
        'target_amount',
        'achieved_amount',
        'total_quotations',
        'converted_pos',
        'conversion_rate',
    ];

    protected function casts(): array
    {
        return [
            'target_amount'   => 'decimal:2',
            'achieved_amount' => 'decimal:2',
            'conversion_rate' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    public function scopeCurrentMonth($query)
    {
        return $query->where('month', now()->month)->where('year', now()->year);
    }

    public function scopeForMonth($query, int $month, int $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    // ─── Computed ─────────────────────────────────────────────────────

    public function getQuotaPercentageAttribute(): float
    {
        if ((float) $this->target_amount <= 0) {
            return 0.0;
        }
        return round(((float) $this->achieved_amount / (float) $this->target_amount) * 100, 1);
    }

    public function getMonthLabelAttribute(): string
    {
        return \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->format('F Y');
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    public function recalculateConversionRate(): void
    {
        $this->conversion_rate = $this->total_quotations > 0
            ? round(($this->converted_pos / $this->total_quotations) * 100, 2)
            : 0.00;
        $this->save();
    }
}
