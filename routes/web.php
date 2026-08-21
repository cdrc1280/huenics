<?php

use App\Http\Controllers\CustomerPortalController;
use App\Models\Document;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

// ─── Public Customer Portal Routes ──────────────────────────────────────────
Route::get('/', [CustomerPortalController::class, 'index'])->name('customer.home');
Route::get('/about', [CustomerPortalController::class, 'about'])->name('customer.about');
Route::get('/products', [CustomerPortalController::class, 'products'])->name('customer.products');
Route::get('/quotation/builder', [CustomerPortalController::class, 'quotationBuilder'])->name('customer.quotation-builder');
Route::post('/quotation/generate-unofficial', [CustomerPortalController::class, 'generateUnofficial'])->name('customer.quotation.generate');
Route::get('/quotation/unofficial/download-pdf', [CustomerPortalController::class, 'downloadLastPdf'])->name('customer.quotation.download-pdf');

Route::get('/login', function () {
    return redirect()->to('/admin/login');
})->name('login');

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/documents/{document}/preview', function (Document $document) {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        // If document has a companion PDF, serve that for seamless browser iframe preview
        if ($document->companion_pdf_path) {
            $compCandidates = [
                storage_path('app/' . $document->companion_pdf_path),
                storage_path('app/private/' . $document->companion_pdf_path),
            ];
            foreach ($compCandidates as $candidate) {
                if (file_exists($candidate)) {
                    return response()->file($candidate, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="' . basename($document->original_filename) . '.pdf"',
                    ]);
                }
            }
        }

        $path = $document->getAbsolutePath();

        if (!$path || !file_exists($path)) {
            abort(404, 'File not found on server.');
        }

        $mime = $document->original_mime_type ?: (mime_content_type($path) ?: 'application/pdf');

        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($document->original_filename) . '"',
        ]);
    })->name('documents.preview');

    Route::get('/documents/{document}/download', function (Document $document) {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        $path = $document->getAbsolutePath();

        if (!$path || !file_exists($path)) {
            abort(404, 'PDF file not found on server.');
        }

        return response()->download($path, $document->original_filename);
    })->name('documents.download');

    Route::get('/documents/{document}/live-pdf', function (Document $document) {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        $payloadRaw = request()->query('payload');
        $payload = [];
        if ($payloadRaw) {
            $decoded = json_decode(base64_decode($payloadRaw), true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        if (empty($payload)) {
            $payload = [
                'documentNumber' => $document->document_number,
                'documentDate' => $document->document_date ? $document->document_date->format('Y-m-d') : null,
                'customerName' => $document->customer_name ?: ($document->project?->customer_name ?: 'Engr. Ronald Rey Sandoval'),
                'customerCompany' => $document->customer_company ?: 'MGS CONSTRUCTION, INC.',
                'projectName' => $document->project_name ?: ($document->project?->name ?: 'Palanza Tower'),
                'projectLocation' => $document->project_location ?: 'Palanza St. corner Guirayan st., Dona Imelda, Q.C',
                'phoneNo' => $document->phone_no ?: '0906-144-2553',
                'items' => $document->lineItems->toArray(),
                'mod' => [],
            ];
        }

        $generator = app(\App\Services\LivePdfGenerator::class);
        $pdfOutput = $generator->generate($payload);

        return response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="live-document.pdf"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    })->name('documents.live-pdf');

    // Quotation PDF Export & Preview
    Route::get('/quotations/{quotation}/export-pdf', function (\App\Models\Quotation $quotation) {
        return app(\App\Services\ExportQuotationPdf::class)->downloadResponse($quotation);
    })->name('quotations.export-pdf');

    Route::get('/quotations/{quotation}/preview-pdf', function (\App\Models\Quotation $quotation) {
        return app(\App\Services\ExportQuotationPdf::class)->previewResponse($quotation);
    })->name('quotations.preview-pdf');

    // Delivery Receipt PDF Export
    Route::get('/delivery-receipts/{deliveryReceipt}/export-pdf', function (\App\Models\DeliveryReceipt $deliveryReceipt) {
        $html = view('pdf.delivery-receipt-template', ['record' => $deliveryReceipt])->render();
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="DR-' . $deliveryReceipt->dr_number . '.pdf"',
        ]);
    })->name('delivery-receipts.export-pdf');

    // Sales Invoice PDF Export
    Route::get('/sales-invoices/{salesInvoice}/export-pdf', function (\App\Models\SalesInvoice $salesInvoice) {
        $html = view('pdf.sales-invoice-template', ['record' => $salesInvoice])->render();
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="SI-' . $salesInvoice->si_number . '.pdf"',
        ]);
    })->name('sales-invoices.export-pdf');
});

