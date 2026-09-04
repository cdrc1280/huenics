<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\User;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ExportExecutiveReportPdf
{
    /**
     * Generate PDF binary content for the Executive Sales Report.
     */
    public function generate(array $filterData = []): string
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);

        $reportData = $this->buildReportData($filterData);

        $html = View::make('pdf.executive-report-template', $reportData)->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Return a file download response for the Executive Sales Report PDF.
     */
    public function downloadResponse(array $filterData = []): Response
    {
        $pdfContent = $this->generate($filterData);
        $periodLabel = preg_replace('/[^a-zA-Z0-9_-]/', '_', $this->resolvePeriodLabel($filterData));
        $filename = 'huenics-executive-sales-report-' . strtolower($periodLabel) . '-' . date('Ymd') . '.pdf';

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    protected function resolvePeriodLabel(array $filterData): string
    {
        [$startDate, $endDate, $periodLabel] = $this->getDateRange($filterData);
        return $periodLabel;
    }

    public function getDateRange(array $filterData): array
    {
        $periodType = $filterData['periodType'] ?? 'month';
        $year = (int) ($filterData['selectedYear'] ?? now()->year);

        switch ($periodType) {
            case 'days':
                $date = !empty($filterData['selectedDate'])
                    ? Carbon::parse($filterData['selectedDate'])->startOfDay()
                    : now()->startOfDay();
                return [
                    $date,
                    $date->copy()->endOfDay(),
                    $date->format('F d, Y'),
                ];

            case 'weeks':
                $week = (int) ($filterData['selectedWeek'] ?? now()->weekOfYear);
                $start = Carbon::now()->setISODate($year, $week)->startOfWeek();
                $end = $start->copy()->endOfWeek();
                return [
                    $start,
                    $end,
                    "Week {$week} (" . $start->format('M d') . " – " . $end->format('M d, Y') . ")",
                ];

            case 'years':
                $start = Carbon::create($year, 1, 1)->startOfYear();
                $end = Carbon::create($year, 12, 31)->endOfYear();
                return [
                    $start,
                    $end,
                    "Year {$year}",
                ];

            case 'month':
            default:
                $month = (int) ($filterData['selectedMonth'] ?? now()->month);
                $start = Carbon::create($year, $month, 1)->startOfMonth();
                $end = $start->copy()->endOfMonth();
                return [
                    $start,
                    $end,
                    $start->format('F Y'),
                ];
        }
    }

    public function buildReportData(array $filterData): array
    {
        [$startDate, $endDate, $periodLabel] = $this->getDateRange($filterData);
        $startStr = $startDate->copy()->startOfDay()->toDateTimeString();
        $endStr = $endDate->copy()->endOfDay()->toDateTimeString();
        $startDateOnly = $startDate->toDateString();
        $endDateOnly = $endDate->toDateString();

        $selectedAgentId = $filterData['selectedAgentId'] ?? null;
        $filterInhouse = (bool) ($filterData['filterInhouse'] ?? false);

        $poDateScope = function ($q) use ($startStr, $endStr, $startDateOnly, $endDateOnly) {
            $q->where(function ($sub) use ($startStr, $endStr, $startDateOnly, $endDateOnly) {
                $sub->whereBetween('order_date', [$startStr, $endStr])
                    ->orWhere(fn($s) => $s->whereDate('order_date', '>=', $startDateOnly)->whereDate('order_date', '<=', $endDateOnly))
                    ->orWhereBetween('actual_delivery_date', [$startDateOnly, $endDateOnly])
                    ->orWhereBetween('completed_at', [$startStr, $endStr])
                    ->orWhereBetween('created_at', [$startStr, $endStr]);
            });
        };

        $qDateScope = function ($q) use ($startStr, $endStr, $startDateOnly, $endDateOnly) {
            $q->where(function ($sub) use ($startStr, $endStr, $startDateOnly, $endDateOnly) {
                $sub->whereBetween('quotation_date', [$startStr, $endStr])
                    ->orWhere(fn($s) => $s->whereDate('quotation_date', '>=', $startDateOnly)->whereDate('quotation_date', '<=', $endDateOnly))
                    ->orWhereBetween('created_at', [$startStr, $endStr]);
            });
        };

        $query = User::query()
            ->whereIn('role', [
                User::ROLE_SALES_EXECUTIVE,
                User::ROLE_ADMIN,
                User::ROLE_OPERATIONS_MANAGER,
                User::ROLE_CEO,
            ])
            ->withCount([
                'quotations as period_quotations' => $qDateScope,
                'purchaseOrders as period_pos' => fn($q) => $q->whereNotIn('status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED])->where($poDateScope),
            ])
            ->withSum([
                'purchaseOrders as period_achieved' => fn($q) => $q->whereNotIn('status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED])->where($poDateScope),
            ], 'order_amount')
            ->withSum([
                'purchaseOrders as period_profit' => fn($q) => $q->whereNotIn('status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED])->where($poDateScope),
            ], 'realized_profit');

        if ($filterInhouse) {
            $query->where('is_owner', true);
        } elseif ($selectedAgentId) {
            $query->where('id', $selectedAgentId);
        }

        $users = $query->orderByDesc('period_achieved')->get();

        $leaderboard = [];
        $totalSales = 0.0;
        $totalProfit = 0.0;
        $totalQuotes = 0;
        $totalPos = 0;

        foreach ($users as $user) {
            $achieved = (float) ($user->period_achieved ?? 0);
            $profit = (float) ($user->period_profit ?? 0);
            $quotesCount = (int) ($user->period_quotations ?? 0);
            $posCount = (int) ($user->period_pos ?? 0);
            $winRate = $quotesCount > 0 ? round(($posCount / $quotesCount) * 100, 1) : 0;

            $totalSales += $achieved;
            $totalProfit += $profit;
            $totalQuotes += $quotesCount;
            $totalPos += $posCount;

            $leaderboard[] = [
                'name'           => $user->name,
                'is_owner'       => $user->is_owner,
                'role_label'     => $user->is_owner ? 'Inhouse (Owner)' : ucfirst(str_replace('_', ' ', $user->role)),
                'sales_achieved' => $achieved,
                'profit'         => $profit,
                'quotations'     => $quotesCount,
                'pos'            => $posCount,
                'win_rate'       => $winRate . '%',
                'win_rate_val'   => $winRate,
            ];
        }

        // Overall Quotation pipeline amount
        $quoteQuery = Quotation::whereNotIn('status', [Quotation::STATUS_REJECTED])->where($qDateScope);
        if ($filterInhouse) {
            $quoteQuery->whereHas('salesAgent', fn($u) => $u->where('is_owner', true));
        } elseif ($selectedAgentId) {
            $quoteQuery->where('sales_agent_id', $selectedAgentId);
        }
        $totalQuotedAmount = (float) $quoteQuery->sum('total_amount');

        // Delivered POs
        $deliveredPoQuery = PurchaseOrder::where('delivery_status', PurchaseOrder::DELIVERY_DELIVERED)
            ->whereNotIn('status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED])
            ->where($poDateScope);
        if ($filterInhouse) {
            $deliveredPoQuery->whereHas('salesAgent', fn($u) => $u->where('is_owner', true));
        } elseif ($selectedAgentId) {
            $deliveredPoQuery->where('sales_agent_id', $selectedAgentId);
        }
        $deliveredCount = $deliveredPoQuery->count();
        $deliveredAmount = (float) $deliveredPoQuery->sum('order_amount');

        $overallMarginPct = $totalSales > 0 ? round(($totalProfit / $totalSales) * 100, 1) : 0;
        $overallWinRate = $totalQuotes > 0 ? round(($totalPos / $totalQuotes) * 100, 1) : 0;

        $scopeLabel = 'All Accounts & Sales Executives';
        if ($filterInhouse) {
            $scopeLabel = 'Inhouse / Owner Accounts Only';
        } elseif ($selectedAgentId) {
            $agent = User::find($selectedAgentId);
            $scopeLabel = 'Sales Executive: ' . ($agent?->name ?? "Agent #{$selectedAgentId}");
        }

        return [
            'periodLabel' => $periodLabel,
            'scopeLabel'  => $scopeLabel,
            'generatedAt' => now()->format('F d, Y h:i A'),
            'leaderboard' => $leaderboard,
            'kpis'        => [
                'total_sales'         => $totalSales,
                'total_profit'        => $totalProfit,
                'margin_pct'          => $overallMarginPct,
                'total_quotations'    => $totalQuotes,
                'total_pos'           => $totalPos,
                'win_rate'            => $overallWinRate,
                'total_quoted_amount' => $totalQuotedAmount,
                'delivered_pos'       => $deliveredCount,
                'delivered_amount'    => $deliveredAmount,
            ],
        ];
    }
}
