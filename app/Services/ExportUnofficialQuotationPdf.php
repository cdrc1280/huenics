<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ExportUnofficialQuotationPdf
{
    /**
     * Generate PDF binary content for an unofficial customer quotation.
     *
     * @param array<string, mixed> $quotationData
     */
    public function generate(array $quotationData): string
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);

        // Ensure defaults for calculations
        $items = $quotationData['items'] ?? [];
        $subtotal = 0.0;
        foreach ($items as &$item) {
            $qty = (float) ($item['quantity'] ?? $item['qty'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            $lineTotal = isset($item['line_total']) ? (float) $item['line_total'] : round($qty * $price, 2);
            $item['quantity'] = $qty;
            $item['unit_price'] = $price;
            $item['line_total'] = $lineTotal;
            $subtotal += $lineTotal;
        }
        unset($item);

        $quotationData['items'] = $items;
        $quotationData['subtotal'] = round($subtotal, 2);
        $quotationData['vat_amount'] = round($subtotal * 0.12, 2);
        $quotationData['grand_total'] = round($subtotal + $quotationData['vat_amount'], 2);

        $html = View::make('pdf.unofficial-quotation-template', [
            'quote' => $quotationData,
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Return a file download response for the unofficial quotation PDF.
     *
     * @param array<string, mixed> $quotationData
     */
    public function downloadResponse(array $quotationData): Response
    {
        $pdfContent = $this->generate($quotationData);
        $refNumber = $quotationData['quotation_number'] ?? ('UNOFF-' . date('Ymd-His'));

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $refNumber . '.pdf"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Return an inline browser preview response for the unofficial quotation PDF.
     *
     * @param array<string, mixed> $quotationData
     */
    public function previewResponse(array $quotationData): Response
    {
        $pdfContent = $this->generate($quotationData);
        $refNumber = $quotationData['quotation_number'] ?? ('UNOFF-' . date('Ymd-His'));

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $refNumber . '.pdf"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
