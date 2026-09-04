<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class LivePdfGenerator
{
    public function generate(array $data): string
    {
        // Custom autoloader fallback for Dompdf if needed
        if (!class_exists(\Dompdf\Dompdf::class)) {
            spl_autoload_register(function ($class) {
                if (str_starts_with($class, 'Dompdf\\')) {
                    $file = base_path('vendor/dompdf/dompdf/src/' . str_replace('\\', '/', substr($class, 7)) . '.php');
                    if (file_exists($file)) {
                        require_once $file;
                        return;
                    }
                }
                if (str_starts_with($class, 'FontLib\\')) {
                    $file = base_path('vendor/dompdf/php-font-lib/src/FontLib/' . str_replace('\\', '/', substr($class, 8)) . '.php');
                    if (file_exists($file)) {
                        require_once $file;
                        return;
                    }
                }
                if (str_starts_with($class, 'Svg\\')) {
                    $file = base_path('vendor/dompdf/php-svg-lib/src/Svg/' . str_replace('\\', '/', substr($class, 4)) . '.php');
                    if (file_exists($file)) {
                        require_once $file;
                        return;
                    }
                }
                if (str_starts_with($class, 'Sabberworm\\CSS\\')) {
                    $file = base_path('vendor/sabberworm/php-css-parser/src/' . str_replace('\\', '/', substr($class, 15)) . '.php');
                    if (file_exists($file)) {
                        require_once $file;
                        return;
                    }
                }
            });
        }

        $cpdfPath = base_path('vendor/dompdf/dompdf/lib/Cpdf.php');
        if (file_exists($cpdfPath) && !class_exists(\Dompdf\Cpdf::class)) {
            require_once $cpdfPath;
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);

        // Normalize data to clean UTF-8 and standard ASCII slashes to avoid '?' rendering
        $cleanedData = $this->normalizePdfData($data);

        $html = view('pdf.live-document-template', $cleanedData)->render();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private function normalizePdfData(mixed $data): mixed
    {
        if (is_array($data)) {
            return array_map([$this, 'normalizePdfData'], $data);
        }

        if (is_string($data)) {
            // Replace non-ASCII/Unicode alternative slashes with standard ASCII '/'
            $data = str_replace(["\xE2\x88\x95", "\xE2\x81\x84", "\xEF\xBC\x8F", '∕', '⁄', '／'], '/', $data);
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        }

        return $data;
    }
}
