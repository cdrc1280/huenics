<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Quotation;

class PoQuotationReconciler
{
    /**
     * Reconcile line items between a Purchase Order and its linked Quotation.
     *
     * @param PurchaseOrder $po
     * @param Quotation|null $quotation
     * @return array
     */
    public function reconcile(PurchaseOrder $po, ?Quotation $quotation = null): array
    {
        $quotation = $quotation ?: $po->quotation;

        if (!$quotation) {
            return [
                'has_linked_quotation' => false,
                'quotation_number' => null,
                'has_discrepancies' => false,
                'discrepancy_count' => 0,
                'exact_matches_count' => 0,
                'rows' => [],
                'totals' => [
                    'po_total' => (float) $po->order_amount,
                    'quotation_total' => 0,
                    'variance' => 0,
                ],
            ];
        }

        $po->unsetRelation('lineItems');
        $quotation->unsetRelation('lineItems');

        $poLines = $po->lineItems()->get();
        $qLines = $quotation->lineItems()->get();

        // Check if there is an official negotiated amount on the quotation document
        $quotationNegotiatedAmount = null;
        if ($quotation->document && $quotation->document->totals && $quotation->document->totals->negotiated_amount) {
            $quotationNegotiatedAmount = (float) $quotation->document->totals->negotiated_amount;
        }

        $poTotalAmount = (float) ($po->order_amount ?: ($po->document?->totals?->printed_total ?: 0.0));
        $quotationTotalAmount = (float) $quotation->total_amount;
        $effectiveQuotationTargetTotal = $quotationNegotiatedAmount ?: $quotationTotalAmount;

        // Check if overall order total matches quotation target (within 1% or ₱5.00)
        $isNegotiatedOrderMatch = ($effectiveQuotationTargetTotal > 0 && abs($poTotalAmount - $effectiveQuotationTargetTotal) <= max(5.0, $effectiveQuotationTargetTotal * 0.01));

        $rows = [];
        $matchedQCounts = []; // qLineId => total po qty matched
        $poLineMatches = []; // poLineIndex => matchedQLine

        // Pre-compute normalized representations for quotation lines
        $qNormMap = [];
        foreach ($qLines as $qLine) {
            $norm = $this->normalizeDescription($qLine->description ?? '');
            $qNormMap[$qLine->id] = [
                'line' => $qLine,
                'norm' => $norm,
                'tokens' => $this->getTokens($norm),
            ];
        }

        // Pass 1: Find best matching quotation line for each PO line
        $fulfilledQQtys = [];
        foreach ($poLines as $idx => $poLine) {
            $matchedQLine = $this->findBestQuotationMatch($poLine, $qNormMap, $fulfilledQQtys);
            $poLineMatches[$idx] = $matchedQLine;
            if ($matchedQLine) {
                $qId = $matchedQLine->id;
                $matchedQCounts[$qId] = ($matchedQCounts[$qId] ?? 0.0) + (float) $poLine->qty;
                $fulfilledQQtys[$qId] = ($fulfilledQQtys[$qId] ?? 0.0) + (float) $poLine->qty;
            }
        }

        $exactMatchesCount = 0;
        $qtyMismatchesCount = 0;
        $priceMismatchesCount = 0;
        $missingInQuotationCount = 0;

        // Pass 2: Evaluate matching results per PO line
        foreach ($poLines as $idx => $poLine) {
            $matchedQLine = $poLineMatches[$idx];

            if ($matchedQLine) {
                $poQty = (float) $poLine->qty;
                $qQty = (float) $matchedQLine->qty;
                $cumPoQty = (float) ($matchedQCounts[$matchedQLine->id] ?? $poQty);

                // Quantity match: individual line matches OR cumulative split lines fulfill quotation item
                $qtyMatch = (abs($poQty - $qQty) < 0.05) || (abs($cumPoQty - $qQty) < 0.05);

                $poPrice = (float) $poLine->unit_price;
                $qPrice = (float) $matchedQLine->unit_price;
                $qDiscPrice = !empty($matchedQLine->discounted_price) ? (float) $matchedQLine->discounted_price : null;
                $effectiveQPrice = $qDiscPrice ?: $qPrice;

                // Price matching logic
                $priceMatch = false;
                $isNegotiatedPrice = false;

                // 1. Direct price match (discounted or regular within ₱0.05)
                if (abs($poPrice - $effectiveQPrice) < 0.05 || abs($poPrice - $qPrice) < 0.05) {
                    $priceMatch = true;
                }
                // 2. VAT adjustment (12% Philippine VAT: net vs gross)
                elseif (abs(($poPrice * 1.12) - $effectiveQPrice) < 0.05 || abs(($poPrice * 1.12) - $qPrice) < 0.05
                    || abs($poPrice - round($effectiveQPrice / 1.12, 2)) < 0.05) {
                    $priceMatch = true;
                }
                // 3. Negotiated Order Price tolerance (when order total matches negotiated total, or price is within 10%)
                elseif ($isNegotiatedOrderMatch || abs($poPrice - $effectiveQPrice) <= max(10.0, $effectiveQPrice * 0.10)) {
                    $priceMatch = true;
                    $isNegotiatedPrice = true;
                }

                $poTotal = (float) ($poLine->line_total ?: round($poQty * $poPrice, 2));
                $qTotal = (float) ($matchedQLine->line_total ?: round($qQty * $effectiveQPrice, 2));
                $totalMatch = $priceMatch && $qtyMatch;

                $discrepancyNotes = [];
                if (!$qtyMatch) {
                    $qtyDiff = $cumPoQty - $qQty;
                    $sign = $qtyDiff > 0 ? '+' : '';
                    $discrepancyNotes[] = "Qty discrepancy: PO total {$cumPoQty} {$poLine->unit} vs Quoted {$qQty} {$matchedQLine->unit} ({$sign}{$qtyDiff})";
                    $qtyMismatchesCount++;
                }

                if (!$priceMatch) {
                    $priceDiff = $poPrice - $effectiveQPrice;
                    $sign = $priceDiff > 0 ? '+' : '';
                    $formattedDiff = number_format(abs($priceDiff), 2);
                    $discrepancyNotes[] = "Price discrepancy: PO unit price ₱" . number_format($poPrice, 2) . " vs Quoted ₱" . number_format($effectiveQPrice, 2) . " ({$sign}₱{$formattedDiff})";
                    $priceMismatchesCount++;
                } elseif ($isNegotiatedPrice) {
                    $discrepancyNotes[] = "Reflects approved quotation negotiated commercial discount.";
                }

                if ($qtyMatch && $priceMatch) {
                    $status = 'exact_match';
                    $statusLabel = 'Exact Match';
                    $exactMatchesCount++;
                } elseif (!$qtyMatch && !$priceMatch) {
                    $status = 'both_mismatch';
                    $statusLabel = 'Qty & Price Mismatch';
                } elseif (!$qtyMatch) {
                    $status = 'qty_mismatch';
                    $statusLabel = 'Quantity Mismatch';
                } else {
                    $status = 'price_mismatch';
                    $statusLabel = 'Price Mismatch';
                }

                $rows[] = [
                    'item_code' => $poLine->item_code ?: $matchedQLine->item_code,
                    'description' => $poLine->description ?: $matchedQLine->description,
                    'unit' => $poLine->unit ?: $matchedQLine->unit,
                    'po_qty' => $poQty,
                    'quotation_qty' => $qQty,
                    'qty_diff' => round($poQty - $qQty, 2),
                    'qty_match' => $qtyMatch,
                    'po_unit_price' => $poPrice,
                    'quotation_unit_price' => $qPrice,
                    'quotation_discounted_price' => $qDiscPrice,
                    'effective_quotation_price' => $effectiveQPrice,
                    'price_diff' => round($poPrice - $effectiveQPrice, 2),
                    'price_match' => $priceMatch,
                    'po_total' => $poTotal,
                    'quotation_total' => $qTotal,
                    'total_diff' => round($poTotal - $qTotal, 2),
                    'total_match' => $totalMatch,
                    'status' => $status,
                    'status_label' => $statusLabel,
                    'discrepancy_notes' => $discrepancyNotes,
                ];
            } else {
                $missingInQuotationCount++;
                $poQty = (float) $poLine->qty;
                $poPrice = (float) $poLine->unit_price;
                $poTotal = (float) ($poLine->line_total ?: round($poQty * $poPrice, 2));

                $rows[] = [
                    'item_code' => $poLine->item_code,
                    'description' => $poLine->description,
                    'unit' => $poLine->unit,
                    'po_qty' => $poQty,
                    'quotation_qty' => null,
                    'qty_diff' => $poQty,
                    'qty_match' => false,
                    'po_unit_price' => $poPrice,
                    'quotation_unit_price' => null,
                    'quotation_discounted_price' => null,
                    'effective_quotation_price' => null,
                    'price_diff' => $poPrice,
                    'price_match' => false,
                    'po_total' => $poTotal,
                    'quotation_total' => 0.0,
                    'total_diff' => $poTotal,
                    'total_match' => false,
                    'status' => 'missing_in_quotation',
                    'status_label' => 'Not in Quotation',
                    'discrepancy_notes' => ["Item ordered in PO was not included in linked Quotation #{$quotation->quotation_number}."],
                ];
            }
        }

        // Check for quotation items not ordered in PO
        $missingInPoCount = 0;
        foreach ($qLines as $qLine) {
            if (!isset($matchedQCounts[$qLine->id])) {
                $missingInPoCount++;
                $qQty = (float) $qLine->qty;
                $qPrice = (float) $qLine->unit_price;
                $qDiscPrice = !empty($qLine->discounted_price) ? (float) $qLine->discounted_price : null;
                $effectiveQPrice = $qDiscPrice ?: $qPrice;
                $qTotal = (float) ($qLine->line_total ?: round($qQty * $effectiveQPrice, 2));

                $rows[] = [
                    'item_code' => $qLine->item_code,
                    'description' => $qLine->description,
                    'unit' => $qLine->unit,
                    'po_qty' => null,
                    'quotation_qty' => $qQty,
                    'qty_diff' => -$qQty,
                    'qty_match' => false,
                    'po_unit_price' => null,
                    'quotation_unit_price' => $qPrice,
                    'quotation_discounted_price' => $qDiscPrice,
                    'effective_quotation_price' => $effectiveQPrice,
                    'price_diff' => -$effectiveQPrice,
                    'price_match' => false,
                    'po_total' => 0.0,
                    'quotation_total' => $qTotal,
                    'total_diff' => -$qTotal,
                    'total_match' => false,
                    'status' => 'missing_in_po',
                    'status_label' => 'Missing from PO',
                    'discrepancy_notes' => ["Item was approved in Quotation #{$quotation->quotation_number} but not ordered in this PO."],
                ];
            }
        }

        $totalDiscrepancies = $qtyMismatchesCount + $priceMismatchesCount + $missingInQuotationCount + $missingInPoCount;

        return [
            'has_linked_quotation' => true,
            'quotation_id' => $quotation->id,
            'quotation_number' => $quotation->quotation_number,
            'has_discrepancies' => $totalDiscrepancies > 0,
            'discrepancy_count' => $totalDiscrepancies,
            'exact_matches_count' => $exactMatchesCount,
            'qty_mismatches_count' => $qtyMismatchesCount,
            'price_mismatches_count' => $priceMismatchesCount,
            'missing_in_quotation_count' => $missingInQuotationCount,
            'missing_in_po_count' => $missingInPoCount,
            'rows' => $rows,
            'totals' => [
                'po_total' => $poTotalAmount,
                'quotation_total' => $effectiveQuotationTargetTotal,
                'variance' => round($poTotalAmount - $effectiveQuotationTargetTotal, 2),
            ],
        ];
    }

    /**
     * Find best quotation match for a PO line item.
     */
    protected function findBestQuotationMatch($poLine, array $qNormMap, array $fulfilledQQtys = []): ?\App\Models\QuotationLineItem
    {
        // 1. Match by product_id
        if (!empty($poLine->product_id)) {
            foreach ($qNormMap as $entry) {
                if ($entry['line']->product_id == $poLine->product_id) {
                    return $entry['line'];
                }
            }
        }

        // 2. Match by item_code
        if (!empty($poLine->item_code)) {
            $cleanCode = strtolower(trim($poLine->item_code));
            foreach ($qNormMap as $entry) {
                if (!empty($entry['line']->item_code) && strtolower(trim($entry['line']->item_code)) === $cleanCode) {
                    return $entry['line'];
                }
            }
        }

        // 3. Match by description
        if (empty($poLine->description)) {
            return null;
        }

        $normPo = $this->normalizeDescription($poLine->description);
        $poTokens = $this->getTokens($normPo);
        $poQty = (float) $poLine->qty;

        $bestLine = null;
        $bestScore = -10.0;

        foreach ($qNormMap as $qId => $entry) {
            $qLine = $entry['line'];
            $qQty = (float) $qLine->qty;
            $alreadyFulfilled = (float) ($fulfilledQQtys[$qId] ?? 0.0);
            $remainingNeeded = max(0.0, $qQty - $alreadyFulfilled);

            $score = 0.0;
            if ($normPo === $entry['norm']) {
                $score += 2.0;
            } else {
                $score += $this->scoreMatch($poTokens, $entry['tokens']);
            }

            // Quantity affinity bonus: if PO line qty matches remaining needed quantity for this quotation line
            if ($remainingNeeded > 0 && abs($poQty - $remainingNeeded) < 0.05) {
                $score += 1.5; // Strong match for exact quantity needed
            } elseif ($alreadyFulfilled >= $qQty) {
                $score -= 1.0; // Penalty if this quotation line is already 100% fulfilled
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestLine = $entry['line'];
            }
        }

        if ($bestLine && $bestScore > 0.3) {
            return $bestLine;
        }

        return null;
    }

    /**
     * Normalize description for industrial/electrical products.
     */
    protected function normalizeDescription(string $desc): string
    {
        $d = strtolower($desc);
        // Degree abbreviations
        $d = preg_replace('/(\b90\s*(?:degrees?|deg|°)\b|90°)/i', ' 90deg ', $d);
        // Meter abbreviations
        $d = preg_replace('/(\b3\s*(?:meters?|meter|mtrs?|m)\b)/i', ' 3m ', $d);
        $d = preg_replace('/(\b2\s*(?:meters?|meter|mtrs?|m)\b)/i', ' 2m ', $d);
        $d = preg_replace('/(\b1\s*(?:meters?|meter|mtrs?|m)\b)/i', ' 1m ', $d);
        // Compound electrical terms
        $d = str_replace(['track light', 'track-light'], 'tracklight', $d);
        $d = str_replace(['track bar', 'track-bar'], 'trackbar', $d);
        $d = str_replace(['down light', 'down-light'], 'downlight', $d);
        $d = str_replace(['c.o.b', 'c.o.b.'], 'cob', $d);
        $d = str_replace(['warm white', 'warm-white'], 'warmwhite', $d);
        // Remove noise / filler specs
        $d = preg_replace('/\bwarranty:\s*\d+\s*(?:yrs?|years?)\b/i', '', $d);
        $d = preg_replace('/\(to be verified actual with client\)/i', '', $d);
        $d = str_replace(['for magnetic tracklight', 'for track light', 'citizen japan'], '', $d);
        // Punctuation
        $d = preg_replace('/[,\.\-\/\\(\):_Øø]/', ' ', $d);
        $d = preg_replace('/\s+/', ' ', trim($d));
        return $d;
    }

    /**
     * Extract significant words/tokens from a normalized string.
     */
    protected function getTokens(string $normalized): array
    {
        $stopwords = ['pc', 'pcs', 'color', 'with', 'and', 'for', 'the', 'size', 'casing', 'rev', 'actual'];
        $words = explode(' ', $normalized);
        $tokens = [];
        foreach ($words as $w) {
            $w = trim($w);
            if (strlen($w) >= 2 && !in_array($w, $stopwords)) {
                $tokens[] = $w;
            }
        }
        return array_values(array_unique($tokens));
    }

    /**
     * Compute token similarity with variant discriminator weighting.
     */
    protected function scoreMatch(array $poTokens, array $qTokens): float
    {
        if (empty($poTokens) || empty($qTokens)) return 0.0;
        
        $common = array_intersect($poTokens, $qTokens);
        if (empty($common)) return 0.0;

        $discriminators = ['white', 'black', 'movable', '3m', '2m', '1m', '100w', '200w', '90deg', 'straight', 'cap', 'driver'];
        
        $score = count($common) / max(count($poTokens), count($qTokens));
        
        foreach ($discriminators as $disc) {
            $inPo = in_array($disc, $poTokens);
            $inQ = in_array($disc, $qTokens);
            if ($inPo && $inQ) {
                $score += 0.5;
            } elseif ($inPo !== $inQ) {
                $score -= 0.5;
            }
        }

        return $score;
    }
}
