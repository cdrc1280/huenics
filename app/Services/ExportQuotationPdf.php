<?php

namespace App\Services;

use App\Models\Quotation;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ExportQuotationPdf
{
    public function generate(Quotation $quotation): string
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        
        $agentSignature = null;
        if ($quotation->salesAgent && method_exists($quotation->salesAgent, 'getESignatureAbsolutePath')) {
            $agentSignaturePath = $quotation->salesAgent->getESignatureAbsolutePath();
            if ($agentSignaturePath && file_exists($agentSignaturePath)) {
                $type = pathinfo($agentSignaturePath, PATHINFO_EXTENSION);
                $data = file_get_contents($agentSignaturePath);
                $agentSignature = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }
        
        $approverSignature = null;
        if ($quotation->approver && method_exists($quotation->approver, 'getESignatureAbsolutePath')) {
            $approverSignaturePath = $quotation->approver->getESignatureAbsolutePath();
            if ($approverSignaturePath && file_exists($approverSignaturePath)) {
                $type = pathinfo($approverSignaturePath, PATHINFO_EXTENSION);
                $data = file_get_contents($approverSignaturePath);
                $approverSignature = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }
        
        $html = View::make('pdf.quotation-export-template', [
            'quotation' => $quotation,
            'agentSignature' => $agentSignature,
            'approverSignature' => $approverSignature,
        ])->render();
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return $dompdf->output();
    }
    
    public function downloadResponse(Quotation $quotation): Response
    {
        $pdfContent = $this->generate($quotation);
        
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="quotation-' . $quotation->quotation_number . '.pdf"',
        ]);
    }
    
    public function previewResponse(Quotation $quotation): Response
    {
        $pdfContent = $this->generate($quotation);
        
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="quotation-' . $quotation->quotation_number . '.pdf"',
        ]);
    }
}
