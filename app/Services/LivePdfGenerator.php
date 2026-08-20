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

        $dompdf = new Dompdf($options);

        $html = view('pdf.live-document-template', $data)->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
