<?php

namespace App\Services\DocumentParsers;

use App\Models\VendorLayoutFieldMapping;
use Carbon\Carbon;

class FieldExtractor
{
    /**
     * Extract a single field value given the mapping definition and document text / line context.
     *
     * @param VendorLayoutFieldMapping $mapping
     * @param string $fullText
     * @param array<int, string> $lines
     * @param string|null $currentLine
     * @return mixed
     */
    public function extractField(VendorLayoutFieldMapping $mapping, string $fullText, array $lines, ?string $currentLine = null): mixed
    {
        $rawValue = null;

        switch ($mapping->extraction_strategy) {
            case 'regex_header':
                if ($mapping->regex_pattern) {
                    if (preg_match($mapping->regex_pattern, $fullText, $matches)) {
                        $rawValue = $matches[1] ?? $matches[0];
                    }
                }
                break;

            case 'column_position':
                if ($currentLine !== null && $mapping->column_start !== null) {
                    $start = max(0, $mapping->column_start);
                    $length = ($mapping->column_end !== null && $mapping->column_end > $start)
                        ? ($mapping->column_end - $start)
                        : null;

                    $rawValue = ($length !== null)
                        ? substr($currentLine, $start, $length)
                        : substr($currentLine, $start);
                }
                break;

            case 'keyword_offset':
                if ($mapping->regex_pattern && preg_match($mapping->regex_pattern, $fullText, $matches)) {
                    $rawValue = $matches[1] ?? null;
                }
                break;

            case 'table_row_index':
                if ($mapping->row_offset !== null && isset($lines[$mapping->row_offset])) {
                    $rawValue = $lines[$mapping->row_offset];
                }
                break;
        }

        return $this->postProcess($rawValue, $mapping->post_process);
    }

    /**
     * Extract a field value using an array of extraction rules (from layout header_rules JSON).
     * This supports the vendor-specific layout configurations stored in vendor_document_layouts.header_rules.
     *
     * @param string $fullText
     * @param array<int, string> $lines
     * @param array|string $rules  Either a single rule array or an array of rule arrays
     * @return string|null
     */
    public function extractByRules(string $fullText, array $lines, array|string $rules): ?string
    {
        // If rules is a string (simple regex pattern), wrap it
        if (is_string($rules)) {
            $rules = [['extraction_strategy' => 'regex_header', 'regex_pattern' => $rules, 'post_process' => 'trim']];
        }

        // If rules is a single rule definition (has 'extraction_strategy' key), wrap it
        if (isset($rules['extraction_strategy'])) {
            $rules = [$rules];
        }

        foreach ($rules as $rule) {
            $strategy = $rule['extraction_strategy'] ?? 'regex_header';
            $pattern = $rule['regex_pattern'] ?? null;
            $postProcess = $rule['post_process'] ?? 'trim';
            $rawValue = null;

            switch ($strategy) {
                case 'regex_header':
                case 'keyword_offset':
                    if ($pattern && preg_match($pattern, $fullText, $m)) {
                        $rawValue = $m[1] ?? $m[0];
                    }
                    break;

                case 'column_position':
                    $colStart = $rule['column_start'] ?? null;
                    $colEnd = $rule['column_end'] ?? null;
                    if ($colStart !== null) {
                        foreach ($lines as $line) {
                            $start = max(0, $colStart);
                            $length = ($colEnd !== null && $colEnd > $start) ? ($colEnd - $start) : null;
                            $extracted = ($length !== null) ? substr($line, $start, $length) : substr($line, $start);
                            $extracted = trim($extracted);
                            if (!empty($extracted) && preg_match('/\d/', $extracted)) {
                                $rawValue = $extracted;
                                break;
                            }
                        }
                    }
                    break;

                case 'table_row_index':
                    $rowOffset = $rule['row_offset'] ?? null;
                    if ($rowOffset !== null && isset($lines[$rowOffset])) {
                        $rawValue = $lines[$rowOffset];
                    }
                    break;
            }

            if ($rawValue !== null && trim($rawValue) !== '') {
                return $this->postProcess(trim($rawValue), $postProcess);
            }
        }

        return null;
    }

    /**
     * Apply formatting and data type normalization.
     */
    public function postProcess(mixed $value, string $action): mixed
    {
        if ($value === null) {
            return null;
        }

        $str = trim((string) $value);

        return match ($action) {
            'strip_commas' => str_replace(',', '', $str),
            'parse_decimal' => (float) preg_replace('/[^\d\.\-]/', '', str_replace(',', '', $str)),
            'parse_int' => (int) preg_replace('/[^\d\-]/', '', $str),
            'parse_date' => $this->parseDate($str),
            'uppercase' => mb_strtoupper($str),
            'trim' => trim($str),
            default => $str,
        };
    }

    protected function parseDate(string $str): ?string
    {
        try {
            $cleaned = trim(preg_replace('/[^\w\s\/\-\,\.]/', '', $str));
            $carbon = Carbon::parse($cleaned);
            return $carbon->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    public function extractPaymentTerms(string $text): ?string
    {
        if (preg_match('/(?:Payment\s*Terms?|Terms?\s*of\s*Payment)\s*[:\-\.]?\s*([^\n\r]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    public function extractDeliveryTerms(string $text): ?string
    {
        if (preg_match('/(?:Delivery\s*Terms?|Terms?\s*of\s*Delivery)\s*[:\-\.]?\s*([^\n\r]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    public function extractTermsAndConditions(string $text): ?string
    {
        if (preg_match('/Terms\s*(?:and|&)\s*Conditions\s*[:\-\.]?\s*(.*?)(?=\n\s*(?:Total|Signed|Approved|Conforme|Prepared)|$)/is', $text, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }
}
