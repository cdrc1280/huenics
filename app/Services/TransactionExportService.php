<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionExportService
{
    /**
     * Generate standard CSV output with UTF-8 BOM for transactions.
     *
     * @param  iterable<Transaction>|Builder|null  $transactions
     */
    public function exportCsv(iterable|Builder|null $transactions = null): string
    {
        if ($transactions === null) {
            $transactions = Transaction::with([
                'project',
                'vendor',
                'purchaseOrder',
                'quotationDocument',
                'purchaseOrderDocument',
                'deliveryReceiptDocument',
                'salesInvoiceDocument',
                'creator',
            ])->latest('created_at')->get();
        } elseif ($transactions instanceof Builder) {
            $transactions = $transactions->with([
                'project',
                'vendor',
                'purchaseOrder',
                'quotationDocument',
                'purchaseOrderDocument',
                'deliveryReceiptDocument',
                'salesInvoiceDocument',
                'creator',
            ])->get();
        }

        $stream = fopen('php://temp', 'r+');

        // Prepend UTF-8 BOM to ensure seamless rendering across Microsoft Excel, Apple Numbers, and LibreOffice
        fwrite($stream, "\xEF\xBB\xBF");

        fputcsv($stream, [
            'Transaction Code',
            'Project',
            'Vendor',
            'Purchase Order No.',
            'Quotation Document No.',
            'PO Document No.',
            'Delivery Receipt No.',
            'Sales Invoice No.',
            'Final Amount (PHP)',
            'Order Date',
            'Delivery Date',
            'Transaction Status',
            'Fulfillment Status',
            'Is Completed',
            'Notes',
            'Created By',
            'Created At',
        ]);

        foreach ($transactions as $transaction) {
            $fulfillmentStatus = match (true) {
                $transaction->is_completed || $transaction->hasFulfillmentDocuments() => 'Completed & Realized',
                $transaction->status === 'delivered' => 'Delivered (Awaiting DR & SI)',
                default => 'Pending Delivery',
            };

            fputcsv($stream, $this->sanitizeCsvRow([
                $transaction->transaction_code ?: "TRX-{$transaction->id}",
                $transaction->project?->name ?? 'N/A',
                $transaction->vendor?->name ?? 'N/A',
                $transaction->purchaseOrder?->po_number ?? 'N/A',
                $transaction->quotationDocument?->document_number ?? 'N/A',
                $transaction->purchaseOrderDocument?->document_number ?? 'N/A',
                $transaction->deliveryReceiptDocument?->document_number ?? 'N/A',
                $transaction->salesInvoiceDocument?->document_number ?? 'N/A',
                number_format((float) $transaction->final_amount, 2, '.', ''),
                $transaction->order_date?->format('Y-m-d') ?? '',
                $transaction->delivery_date?->format('Y-m-d') ?? '',
                ucwords(str_replace('_', ' ', (string) $transaction->status)),
                $fulfillmentStatus,
                $transaction->is_completed ? 'Yes' : 'No',
                $transaction->notes ?? '',
                $transaction->creator?->name ?? 'System',
                $transaction->created_at?->format('Y-m-d H:i:s') ?? '',
            ]));
        }

        rewind($stream);
        $csvContent = stream_get_contents($stream);
        fclose($stream);

        return $csvContent !== false ? $csvContent : '';
    }

    /**
     * Export a single transaction to CSV.
     */
    public function exportSingleTransactionCsv(Transaction $transaction): string
    {
        $transaction->loadMissing([
            'project',
            'vendor',
            'purchaseOrder',
            'quotationDocument',
            'purchaseOrderDocument',
            'deliveryReceiptDocument',
            'salesInvoiceDocument',
            'creator',
        ]);

        return $this->exportCsv(new Collection([$transaction]));
    }

    /**
     * Stream CSV download response.
     *
     * @param  iterable<Transaction>|Builder|null  $transactions
     */
    public function downloadCsvResponse(iterable|Builder|null $transactions = null, ?string $filename = null): StreamedResponse
    {
        $filename = $filename ?: 'huenics-transactions-'.date('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($transactions): void {
            echo $this->exportCsv($transactions);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Sanitize array values to guaranteed valid UTF-8 strings.
     *
     * @param  array<int, mixed>  $row
     * @return array<int, string>
     */
    private function sanitizeCsvRow(array $row): array
    {
        return array_map(function ($value): string {
            if ($value === null) {
                return '';
            }
            $str = (string) $value;
            if (! mb_check_encoding($str, 'UTF-8')) {
                $str = mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
            }

            return $str;
        }, $row);
    }
}
