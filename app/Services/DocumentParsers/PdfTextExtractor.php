<?php

namespace App\Services\DocumentParsers;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as SmalotParser;
use Dompdf\Dompdf;

class PdfTextExtractor
{
    /**
     * Extract raw layout text from a PDF or image file.
     * Uses pdftotext CLI with -layout option if available for high fidelity tabular layout,
     * with automatic fallback to Smalot\PdfParser.
     *
     * @param string $filePath Absolute path to file
     * @return array{text: string, lines: array<int, string>, engine: string, companion_pdf: ?string}
     */
    public function extract(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found at: {$filePath}");
        }

        $isImage = false;
        $mime = mime_content_type($filePath);
        if ($mime && str_starts_with($mime, 'image/')) {
            $isImage = true;
        } else {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'bmp', 'tif', 'tiff'])) {
                $isImage = true;
            }
        }

        $companionPdf = null;

        if ($isImage) {
            $companionPdf = $this->convertImageToPdf($filePath);

            $scriptPath = base_path('app/Services/DocumentParsers/ocr_runner.py');
            $pythonCandidates = [
                'C:\\laragon\\bin\\python\\python-3.10\\python.exe',
                'C:\\Python313\\python.exe',
                'python',
                'C:\\Python312\\python.exe',
                'py',
            ];

            foreach ($pythonCandidates as $py) {
                try {
                    $process = Process::run("\"{$py}\" \"{$scriptPath}\" --image \"{$filePath}\"");
                    if ($process->successful()) {
                        $output = $process->output();
                        $json = json_decode(trim($output), true);
                        if ($json && isset($json['success']) && $json['success'] === true && !empty(trim($json['text']))) {
                            return [
                                'text' => $json['text'],
                                'lines' => $json['lines'] ?? [],
                                'engine' => 'winocr-native',
                                'companion_pdf' => $companionPdf,
                            ];
                        }
                    }
                } catch (Exception $e) {
                    Log::warning("WinOCR attempt with {$py} failed: " . $e->getMessage());
                }
            }

            try {
                $process = Process::run("tesseract \"{$filePath}\" stdout --psm 6");
                if ($process->successful() && !empty(trim($process->output()))) {
                    $text = $process->output();
                    $lines = preg_split('/\r\n|\r|\n/', $text);
                    return [
                        'text' => $text,
                        'lines' => $lines ?: [],
                        'engine' => 'tesseract-ocr',
                        'companion_pdf' => $companionPdf,
                    ];
                }
                
                // Fallback without psm
                $process = Process::run("tesseract \"{$filePath}\" stdout");
                if ($process->successful() && !empty(trim($process->output()))) {
                    $text = $process->output();
                    $lines = preg_split('/\r\n|\r|\n/', $text);
                    return [
                        'text' => $text,
                        'lines' => $lines ?: [],
                        'engine' => 'tesseract-ocr',
                        'companion_pdf' => $companionPdf,
                    ];
                }
            } catch (Exception $e) {
                Log::warning("Tesseract OCR failed: " . $e->getMessage());
            }

            return [
                'text' => '[Image Extraction Failed or Empty]',
                'lines' => ['[Image Extraction Failed or Empty]'],
                'engine' => 'fallback',
                'companion_pdf' => $companionPdf,
            ];
        }

        // Try high-fidelity in-order stream extraction via Smalot Parser first
        try {
            $phpText = $this->extractViaPhp($filePath);
            if (!empty(trim($phpText))) {
                $lines = preg_split('/\r\n|\r|\n/', $phpText);
                return [
                    'text' => $phpText,
                    'lines' => $lines ?: [],
                    'engine' => 'smalot-pdfparser',
                    'companion_pdf' => null,
                ];
            }
        } catch (\Throwable $e) {
            Log::debug("Smalot parser pass failed: " . $e->getMessage());
        }

        // Fallback to pdftotext CLI
        $cliText = $this->extractViaCli($filePath);
        if ($cliText !== null && strlen(trim($cliText)) > 0) {
            $lines = preg_split('/\r\n|\r|\n/', $cliText);
            return [
                'text' => $cliText,
                'lines' => $lines ?: [],
                'engine' => 'pdftotext-cli',
                'companion_pdf' => null,
            ];
        }

        return [
            'text' => '',
            'lines' => [],
            'engine' => 'empty',
            'companion_pdf' => null,
        ];
    }

    protected function convertImageToPdf(string $imagePath): ?string
    {
        try {
            $imageData = base64_encode(file_get_contents($imagePath));
            $mime = mime_content_type($imagePath) ?: 'image/jpeg';
            $src = 'data:' . $mime . ';base64,' . $imageData;

            $html = '<!DOCTYPE html><html><head><style>body,html{margin:0;padding:0;text-align:center;} img{max-width:100%;max-height:100%;}</style></head><body><img src="'.$src.'"></body></html>';

            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $output = $dompdf->output();
            
            $filename = pathinfo($imagePath, PATHINFO_FILENAME) . '_' . time() . '.pdf';
            $relPath = 'private/documents/companions/' . $filename;
            Storage::disk('local')->put($relPath, $output);
            
            return $relPath;
        } catch (Exception $e) {
            Log::error("Failed to convert image to PDF: " . $e->getMessage());
            return null;
        }
    }

    protected function extractViaCli(string $pdfPath): ?string
    {
        try {
            // Check if pdftotext is available in system PATH
            $process = Process::run("pdftotext -layout \"{$pdfPath}\" -");
            if ($process->successful()) {
                return $process->output();
            }
        } catch (Exception $e) {
            Log::debug("CLI pdftotext failed or not found: " . $e->getMessage());
        }

        return null;
    }

    protected function extractViaPhp(string $pdfPath): string
    {
        try {
            $parser = new SmalotParser();
            $pdf = $parser->parseFile($pdfPath);
            return $pdf->getText();
        } catch (Exception $e) {
            Log::error("Smalot PDF parsing failed: " . $e->getMessage());
            throw new Exception("Failed to parse PDF content: " . $e->getMessage());
        }
    }
}
