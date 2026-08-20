<?php

namespace App\Jobs;

use App\Actions\ReconcileDocumentTotals;
use App\Models\Document;
use App\Services\DocumentParsers\DynamicDocumentParser;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExtractDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Document $document) {}

    public function handle(DynamicDocumentParser $parser, ReconcileDocumentTotals $reconciler): void
    {
        try {
            $this->document->update(['status' => Document::STATUS_PROCESSING]);

            // 1. Run dynamic text extraction & table parsing
            $result = $parser->parseDocument($this->document);

            if (!$result['success']) {
                $this->document->update([
                    'status' => Document::STATUS_FAILED,
                    'failure_reason' => $result['message'],
                ]);
                return;
            }

            // 2. Run arithmetic and VAT reconciliation
            $totals = $reconciler->execute($this->document);

            // 3. Mark as requires_review for user verification
            $this->document->update([
                'status' => Document::STATUS_REQUIRES_REVIEW,
            ]);

            Log::info("Document #{$this->document->id} parsed successfully with {$result['line_items_count']} line items.");
        } catch (Exception $e) {
            Log::error("Failed to extract document #{$this->document->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $this->document->update([
                'status' => Document::STATUS_FAILED,
                'failure_reason' => $e->getMessage(),
            ]);
        }
    }
}
