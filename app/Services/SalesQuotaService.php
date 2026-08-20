<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\SalesQuota;
use App\Models\User;
use Illuminate\Support\Collection;

class SalesQuotaService
{
    /**
     * Record a PO conversion toward the sales agent's monthly quota.
     */
    public function recordConversion(PurchaseOrder $po): void
    {
        $agent = $po->salesAgent;
        if (!$agent) {
            return;
        }

        $quota = SalesQuota::firstOrCreate(
            [
                'user_id' => $agent->id,
                'month'   => $po->order_date->month,
                'year'    => $po->order_date->year,
            ],
            ['target_amount' => 0, 'achieved_amount' => 0]
        );

        $quota->increment('achieved_amount', (float) $po->order_amount);
        $quota->increment('converted_pos');
        $quota->recalculateConversionRate();
    }

    /**
     * Get ranked sales leaderboard for a given month/year.
     */
    public function getLeaderboard(int $month, int $year): Collection
    {
        return SalesQuota::with('user')
            ->forMonth($month, $year)
            ->orderByDesc('achieved_amount')
            ->get()
            ->map(function (SalesQuota $quota, int $rank) {
                return [
                    'rank'             => $rank + 1,
                    'agent'            => $quota->user->name ?? 'Unknown',
                    'role'             => $quota->user->role ?? '',
                    'target'           => (float) $quota->target_amount,
                    'achieved'         => (float) $quota->achieved_amount,
                    'quota_pct'        => $quota->quota_percentage,
                    'total_quotations' => $quota->total_quotations,
                    'converted_pos'    => $quota->converted_pos,
                    'conversion_rate'  => (float) $quota->conversion_rate,
                ];
            });
    }

    /**
     * Set or update monthly target for an agent.
     */
    public function setTarget(User $agent, int $month, int $year, float $targetAmount): SalesQuota
    {
        $quota = SalesQuota::firstOrCreate(
            ['user_id' => $agent->id, 'month' => $month, 'year' => $year],
            ['achieved_amount' => 0, 'total_quotations' => 0, 'converted_pos' => 0]
        );

        $quota->update(['target_amount' => $targetAmount]);
        $quota->recalculateConversionRate();

        return $quota;
    }

    /**
     * Get current month summary for an agent.
     */
    public function getCurrentMonthSummary(User $agent): ?SalesQuota
    {
        return SalesQuota::where('user_id', $agent->id)
            ->currentMonth()
            ->first();
    }
}
