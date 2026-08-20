<?php

namespace App\Actions;

use App\Models\Document;
use App\Services\DocumentParsers\DynamicDocumentParser;
use Exception;
use Illuminate\Support\Facades\Log;

class IngestDocumentAction
{
    public function __construct(
        protected DynamicDocumentParser $parser,
        protected ReconcileDocumentTotals $reconciler
    ) {}

    /**
     * Ingest, SHA-256 hash check, parse via Dynamic Per-Vendor Templates, and reconcile a PDF document.
     *
     * @param string $diskPath
     * @param string $originalFilename
     * @param string $documentType
     * @param int|null $vendorId
     * @param int|null $projectId
     * @param int|null $userId
     * @return Document
     * @throws Exception
     */
    public function execute(
        string $diskPath,
        string $originalFilename,
        string $documentType,
        ?int $vendorId = null,
        ?int $projectId = null,
        ?int $userId = null
    ): Document {
        $candidates = [
            storage_path('app/private/' . $diskPath),
            storage_path('app/' . $diskPath),
            storage_path('app/public/' . $diskPath),
            public_path('storage/' . $diskPath),
        ];

        $filePath = null;
        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                $filePath = $candidate;
                break;
            }
        }

        if (!$filePath && file_exists($diskPath)) {
            $filePath = $diskPath;
        }

        $fileHash = null;
        $mimeType = null;
        if ($filePath && file_exists($filePath)) {
            $fileHash = hash_file('sha256', $filePath);
            $mimeType = mime_content_type($filePath);
        } else {
            $fileHash = hash('sha256', $diskPath . '_' . $originalFilename);
        }

        if (!$mimeType) {
            $ext = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
            $mimeType = match ($ext) {
                'pdf' => 'application/pdf',
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                default => 'application/octet-stream',
            };
        }

        $existing = Document::where('file_hash', $fileHash)->first();
        if ($existing) {
            $existing->update([
                'disk_path' => $diskPath,
                'original_filename' => $originalFilename ?: basename($diskPath),
                'original_mime_type' => $mimeType,
                'document_type' => $documentType ?: $existing->document_type,
            ]);
            $this->parser->parseDocument($existing);
            $this->reconciler->execute($existing);
            return $existing;
        }


        $document = Document::create([
            'disk_path' => $diskPath,
            'original_filename' => $originalFilename ?: basename($diskPath),
            'original_mime_type' => $mimeType,
            'document_type' => $documentType,
            'vendor_id' => $vendorId,
            'project_id' => $projectId,
            'uploaded_by' => $userId ?: (auth()->id() ?: 1),
            'status' => Document::STATUS_UPLOADED,
            'file_hash' => $fileHash,
        ]);

        // Trigger dynamic per-vendor template parsing & mathematical reconciliation
        try {
            $this->parser->parseDocument($document);
            $this->reconciler->execute($document);
        } catch (\Throwable $e) {
            Log::warning("Dynamic parsing notice for Document #{$document->id}: " . $e->getMessage());
        }

        return $document;
    }
}
