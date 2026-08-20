<?php

namespace App\Actions;

use App\Models\Document;
use App\Models\DocumentTotal;

class ReconcileDocumentTotals
{
    /**
     * Reconcile line item arithmetic and document totals against Philippine standard VAT.
     *
     * @param Document $document
     * @return DocumentTotal
     */
    public function execute(Document $document): DocumentTotal
    {
        $lineItems = $document->lineItems;
        $computedSubtotal = 0.0;
        $hasLineMismatch = false;

        // 1. Recompute each line item
        foreach ($lineItems as $item) {
            $unitPrice = (float) $item->unit_price;
            $discPrice = (float) ($item->discounted_price ?? 0);
            $effectivePrice = ($discPrice > 0) ? $discPrice : $unitPrice;

            $computed = round(((float) $item->qty) * $effectivePrice, 2);
            $printed = $item->printed_total !== null ? (float) $item->printed_total : $computed;
            $isMismatch = abs($printed - $computed) > 0.01;

            if ($isMismatch) {
                $hasLineMismatch = true;
            }

            $item->update([
                'computed_total' => $computed,
                'total_mismatch' => $isMismatch,
            ]);

            $computedSubtotal += $computed;
        }

        $computedSubtotal = round($computedSubtotal, 2);

        // 2. Recompute Philippine standard 12% VAT
        $computedVat = round($computedSubtotal * 0.12, 2);
        $computedGrandTotal = round($computedSubtotal + $computedVat, 2);

        // 3. Compare with printed totals
        $totals = $document->totals ?? new DocumentTotal(['document_id' => $document->id]);

        $subtotalMismatch = false;
        if ($totals->printed_subtotal !== null && (float) $totals->printed_subtotal > 0) {
            $subtotalMismatch = abs((float) $totals->printed_subtotal - $computedSubtotal) > 1.00;
        }

        $vatMismatch = false;
        if ($totals->printed_vat !== null && (float) $totals->printed_vat > 0) {
            // Flag if printed VAT deviates by more than ₱1.00 from 12% of computed subtotal
            $vatMismatch = abs((float) $totals->printed_vat - $computedVat) > 1.00;
        }

        $totalMismatch = false;
        if ($totals->printed_total !== null && (float) $totals->printed_total > 0) {
            $printed = (float) $totals->printed_total;

            if ($document->document_type === Document::TYPE_VENDORS_AGREEMENT && $totals->negotiated_amount !== null) {
                // For quotations with a negotiated amount, the authoritative sale is negotiated_amount
                $totalMismatch = false;
            } else {
                // Check if printed total matches VAT-inclusive or VAT-exclusive computed total
                $matchesWithVat = abs($printed - $computedGrandTotal) <= 1.00;
                $matchesWithoutVat = abs($printed - $computedSubtotal) <= 1.00;
                $totalMismatch = !($matchesWithVat || $matchesWithoutVat);
            }
        }

        $totals->fill([
            'computed_subtotal' => $computedSubtotal,
            'computed_vat' => $computedVat,
            'computed_grand_total' => $computedGrandTotal,
            'subtotal_mismatch' => $subtotalMismatch,
            'vat_mismatch' => $vatMismatch,
            'total_mismatch' => $totalMismatch || $hasLineMismatch,
        ]);

        $totals->save();

        return $totals;
    }
}
