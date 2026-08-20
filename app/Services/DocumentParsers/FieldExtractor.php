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
}
