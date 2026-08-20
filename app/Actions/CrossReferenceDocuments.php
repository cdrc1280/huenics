<?php

namespace App\Actions;

use App\Models\Document;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class CrossReferenceDocuments
{
    /**
     * Find related companion documents (Quotation, PO, Order Slip) for the same transaction.
     *
     * @param Document $document
     * @return array{
     *   quotation: ?Document,
     *   purchase_order: ?Document,
     *   order_slip: ?Document,
     *   existing_transaction: ?Transaction,
     *   potential_matches: Collection
     * }
     */
    public function execute(Document $document): array
    {
        $vendorId = $document->vendor_id;
        $projectId = $document->project_id;
        $date = $document->document_date ? Carbon::parse($document->document_date) : now();

        $query = Document::where('id', '!=', $document->id)
            ->whereIn('status', [Document::STATUS_VERIFIED, Document::STATUS_REQUIRES_REVIEW]);

        if ($vendorId) {
            $query->where('vendor_id', $vendorId);
        }

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        // Within ±14 days window if date is known
        if ($document->document_date) {
            $query->whereBetween('document_date', [
                $date->copy()->subDays(14)->format('Y-m-d'),
                $date->copy()->addDays(14)->format('Y-m-d')
            ]);
        }

        $potentialMatches = $query->get();

        $quotation = ($document->document_type === Document::TYPE_VENDORS_AGREEMENT)
            ? $document
            : $potentialMatches->where('document_type', Document::TYPE_VENDORS_AGREEMENT)->first();

        $po = ($document->document_type === Document::TYPE_PURCHASE_ORDER)
            ? $document
            : $potentialMatches->where('document_type', Document::TYPE_PURCHASE_ORDER)->first();

        // Check if a transaction already links any of these documents
        $docIds = array_filter([$document->id, $quotation?->id, $po?->id]);
        $existingTransaction = Transaction::where(function ($q) use ($docIds) {
            $q->whereIn('quotation_document_id', $docIds)
              ->orWhereIn('purchase_order_document_id', $docIds);
        })->first();

        return [
            'quotation' => $quotation,
            'purchase_order' => $po,
            'existing_transaction' => $existingTransaction,
            'potential_matches' => $potentialMatches,
        ];
    }
}
