<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PurchaseOrder;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ExportPurchaseOrderPdf
{
    public function generate(PurchaseOrder $purchaseOrder): string
    {
        $purchaseOrder->loadMissing([
            'lineItems.product',
            'salesAgent',
            'quotation',
            'project',
        ]);

        $options = new Options;
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);

        $html = View::make('pdf.purchase-order-export-template', [
            'purchaseOrder' => $purchaseOrder,
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    public function downloadResponse(PurchaseOrder $purchaseOrder): Response
    {
        $pdfContent = $this->generate($purchaseOrder);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="purchase-order-'.$purchaseOrder->po_number.'.pdf"',
        ]);
    }

    public function previewResponse(PurchaseOrder $purchaseOrder): Response
    {
        $pdfContent = $this->generate($purchaseOrder);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="purchase-order-'.$purchaseOrder->po_number.'.pdf"',
        ]);
    }
}
