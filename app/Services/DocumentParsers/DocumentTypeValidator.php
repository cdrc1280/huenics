<?php

namespace App\Services\DocumentParsers;

use App\Models\Document;
use InvalidArgumentException;

class DocumentTypeValidator
{
    /**
     * Detect document type from raw extracted text.
     *
     * @param string $text
     * @return string|null 'vendors_agreement', 'purchase_order', or null
     */
    public function detectType(string $text): ?string
    {
        $upper = mb_strtoupper($text);

        $quotationScore = 0;
        $poScore = 0;

        // ─── Quotation Indicators ─────────────────────────────────────────
        if (str_contains($upper, 'VENDORS AGREEMENT') || str_contains($upper, 'VENDOR\'S AGREEMENT')) {
            $quotationScore += 15;
        }
        if (preg_match('/QUOTATION\s*(?:NO\.?|#|NUMBER|DATE)?/i', $upper)) {
            $quotationScore += 8;
        }
        if (str_contains($upper, 'PRICE QUOTATION') || str_contains($upper, 'FORMAL QUOTATION') || str_contains($upper, 'PRICE ESTIMATE')) {
            $quotationScore += 6;
        }
        if (str_contains($upper, 'DISCOUNTED PRICE')) {
            $quotationScore += 4;
        }
        if (str_contains($upper, 'HOW TO CLAIM THE WARRANTY') || str_contains($upper, 'CLAIM THE WARRANTY')) {
            $quotationScore += 4;
        }
        if (str_contains($upper, 'STOCK AVAILABILITY') || str_contains($upper, 'TERMS OF DELIVERY')) {
            $quotationScore += 3;
        }
        if (str_contains($upper, 'TERMS AND CONDITIONS') && (str_contains($upper, 'VALIDITY') || str_contains($upper, 'NON-RETURNABLE'))) {
            $quotationScore += 3;
        }
        if (str_contains($upper, 'CUSTOMER\'S NAME OVER SIGNATURE')) {
            $quotationScore += 3;
        }

        // ─── Purchase Order Indicators ────────────────────────────────────
        if (preg_match('/\bPURCHASE\s+ORDER\b/i', $upper)) {
            $poScore += 12;
        }
        if (preg_match('/\b(?:P\.?O\.?|PURCHASE\s+ORDER)\s*(?:NO\.?|#|NUMBER)\b/i', $upper)) {
            $poScore += 8;
        }
        if (str_contains($upper, 'DELIVER TO:') || str_contains($upper, 'DELIVER TO')) {
            $poScore += 4;
        }
        if (str_contains($upper, 'DELIVERY DATE:') || str_contains($upper, 'TERMS OF PAYMENT:')) {
            $poScore += 4;
        }
        if (str_contains($upper, 'SEND INVOICE TO:') || str_contains($upper, 'REFERENCE PR NO')) {
            $poScore += 4;
        }
        if (str_contains($upper, 'IMPORTANT INSTRUCTION/COVENANTS') || str_contains($upper, 'PO SELLER\'S INVOICE')) {
            $poScore += 5;
        }
        if (str_contains($upper, 'RECOMMENDED BY:') && str_contains($upper, 'CONFORMED BY:')) {
            $poScore += 4;
        }

        // ─── Scoring Decision ─────────────────────────────────────────────
        // Explicit 'VENDORS AGREEMENT' takes highest precedence over generic PO mention in checkbox
        if (str_contains($upper, 'VENDORS AGREEMENT') || str_contains($upper, 'VENDOR\'S AGREEMENT')) {
            $quotationScore += 10;
        }

        if ($quotationScore >= 6 && $quotationScore > $poScore) {
            return Document::TYPE_VENDORS_AGREEMENT;
        }

        if ($poScore >= 6 && $poScore > $quotationScore) {
            return Document::TYPE_PURCHASE_ORDER;
        }

        // If borderline match
        if ($quotationScore >= 4 && $quotationScore > $poScore) {
            return Document::TYPE_VENDORS_AGREEMENT;
        }
        if ($poScore >= 4 && $poScore > $quotationScore) {
            return Document::TYPE_PURCHASE_ORDER;
        }

        return null;
    }

    /**
     * Validate that the document extracted text matches the expected document type.
     *
     * @param string $extractedText
     * @param string $expectedType
     * @throws InvalidArgumentException
     */
    public function validate(string $extractedText, string $expectedType): void
    {
        $detectedType = $this->detectType($extractedText);

        if ($expectedType === Document::TYPE_VENDORS_AGREEMENT) {
            if ($detectedType === Document::TYPE_PURCHASE_ORDER) {
                throw new InvalidArgumentException(
                    "Invalid File Upload: The uploaded document appears to be a Purchase Order (PO), not a Quotation. Please upload this document under Purchase Orders."
                );
            }

            if ($detectedType !== Document::TYPE_VENDORS_AGREEMENT) {
                throw new InvalidArgumentException(
                    "Invalid File Upload: The uploaded document could not be verified as a valid Quotation or Vendors Agreement Form. Please ensure you upload a valid quotation PDF or image."
                );
            }
        } elseif ($expectedType === Document::TYPE_PURCHASE_ORDER) {
            if ($detectedType === Document::TYPE_VENDORS_AGREEMENT) {
                throw new InvalidArgumentException(
                    "Invalid File Upload: The uploaded document appears to be a Quotation / Vendors Agreement, not a Purchase Order. Please upload this document under Quotations."
                );
            }

            if ($detectedType !== Document::TYPE_PURCHASE_ORDER) {
                throw new InvalidArgumentException(
                    "Invalid File Upload: The uploaded document could not be verified as a valid Purchase Order (PO). Please ensure you upload a valid Purchase Order PDF or image."
                );
            }
        }
    }
}
