<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountingReportService
{
    /**
     * Generate CSV for Receivables & Aging report.
     */
    public function exportReceivablesCsv(?iterable $orders = null): StreamedResponse
    {
        $orders = $orders ?: PurchaseOrder::whereNotIn('status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED])
            ->where(function ($q) {
                $q->where('delivery_status', PurchaseOrder::DELIVERY_DELIVERED)
                  ->orWhere('status', PurchaseOrder::STATUS_DELIVERED);
            })
            ->orderBy('payment_due_date', 'asc')
            ->get();

        $filename = 'Huenics_Receivables_Report_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM to guarantee proper encoding in Excel
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'PO Number',
                'Customer / Account',
                'Project Name',
                'Order Amount (PHP)',
                'Payment Term',
                'Delivery Date',
                'Due Date',
                'Status',
                'Days Overdue / Remaining',
                'PDC Check #',
                'PDC Bank',
                'Account / Counter Ref',
                'Settled At',
            ]);

            foreach ($orders as $po) {
                $days = $po->days_until_due;
                $daysStr = $po->isPaid()
                    ? 'Settled'
                    : ($days !== null ? ($days < 0 ? abs($days) . ' days overdue' : $days . ' days left') : 'No due date');

                fputcsv($handle, [
                    $po->po_number,
                    $po->customer_name,
                    $po->project?->name ?? 'General',
                    number_format((float) $po->order_amount, 2, '.', ''),
                    $po->payment_terms ?: ($po->payment_term_type ? strtoupper($po->payment_term_type) : 'Not Set'),
                    $po->actual_delivery_date ? $po->actual_delivery_date->format('Y-m-d') : '',
                    $po->payment_due_date ? $po->payment_due_date->format('Y-m-d') : '',
                    strtoupper($po->payment_status ?: 'unpaid'),
                    $daysStr,
                    $po->pdc_check_number ?? '',
                    $po->pdc_bank ?? '',
                    $po->payment_account ?? '',
                    $po->paid_at ? $po->paid_at->format('Y-m-d H:i') : '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Generate CSV for Payment History Ledger.
     */
    public function exportPaymentHistoryCsv(?iterable $orders = null): StreamedResponse
    {
        $orders = $orders ?: PurchaseOrder::where('payment_status', PurchaseOrder::PAYMENT_STATUS_PAID)
            ->orderBy('paid_at', 'desc')
            ->get();

        $filename = 'Huenics_Payment_History_Ledger_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'PO Number',
                'Customer / Account',
                'Settlement Date',
                'Order Amount (PHP)',
                'Payment Term Type',
                'PDC Check Number',
                'PDC Bank / Branch',
                'Counter / Account Tag',
                'Delivery Date',
                'Payment Notes',
            ]);

            foreach ($orders as $po) {
                fputcsv($handle, [
                    $po->po_number,
                    $po->customer_name,
                    $po->paid_at ? $po->paid_at->format('Y-m-d H:i') : ($po->actual_delivery_date ? $po->actual_delivery_date->format('Y-m-d') : ''),
                    number_format((float) $po->order_amount, 2, '.', ''),
                    $po->payment_term_type ? strtoupper($po->payment_term_type) : 'COD',
                    $po->pdc_check_number ?? '',
                    $po->pdc_bank ?? '',
                    $po->payment_account ?? '',
                    $po->actual_delivery_date ? $po->actual_delivery_date->format('Y-m-d') : '',
                    $po->payment_notes ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Generate Streamed PDF response for Receivables & Aging Report.
     */
    public function downloadReceivablesPdf(?iterable $orders = null): StreamedResponse
    {
        $orders = $orders ?: PurchaseOrder::whereNotIn('status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED])
            ->where(function ($q) {
                $q->where('delivery_status', PurchaseOrder::DELIVERY_DELIVERED)
                  ->orWhere('status', PurchaseOrder::STATUS_DELIVERED);
            })
            ->orderBy('payment_due_date', 'asc')
            ->get();

        $totalReceivables = (float) $orders->where('payment_status', '!=', PurchaseOrder::PAYMENT_STATUS_PAID)->sum('order_amount');
        $totalCollected = (float) $orders->where('payment_status', PurchaseOrder::PAYMENT_STATUS_PAID)->sum('order_amount');
        $overdueCount = $orders->filter(fn($po) => !$po->isPaid() && $po->days_until_due !== null && $po->days_until_due < 0)->count();
        $warningCount = $orders->filter(fn($po) => !$po->isPaid() && $po->days_until_due !== null && $po->days_until_due >= 0 && $po->days_until_due <= 10)->count();

        $pdf = Pdf::loadView('pdf.accounting-receivables-report', [
            'orders'           => $orders,
            'totalReceivables' => $totalReceivables,
            'totalCollected'   => $totalCollected,
            'overdueCount'     => $overdueCount,
            'warningCount'     => $warningCount,
            'generatedAt'      => now(),
        ])
        ->setPaper('a4', 'landscape')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', true);

        $pdfContent = $pdf->output();
        $filename = 'Huenics_Receivables_Aging_Report_' . now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(function () use ($pdfContent) {
            print($pdfContent);
        }, $filename, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Generate Streamed PDF response for Payment History Ledger.
     */
    public function downloadPaymentHistoryPdf(?iterable $orders = null): StreamedResponse
    {
        $orders = $orders ?: PurchaseOrder::where('payment_status', PurchaseOrder::PAYMENT_STATUS_PAID)
            ->orderBy('paid_at', 'desc')
            ->get();

        $totalPaid = (float) $orders->sum('order_amount');

        $pdf = Pdf::loadView('pdf.accounting-payment-history', [
            'orders'      => $orders,
            'totalPaid'   => $totalPaid,
            'generatedAt' => now(),
        ])
        ->setPaper('a4', 'landscape')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', true);

        $pdfContent = $pdf->output();
        $filename = 'Huenics_Payment_History_Ledger_' . now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(function () use ($pdfContent) {
            print($pdfContent);
        }, $filename, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
