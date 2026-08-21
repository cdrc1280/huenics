<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Document extends Model
{
    use HasFactory;

    public const TYPE_PURCHASE_ORDER = DocumentType::PurchaseOrder->value;
    public const TYPE_ORDER_SLIP = DocumentType::OrderSlip->value;
    public const TYPE_VENDORS_AGREEMENT = DocumentType::VendorsAgreement->value;

    public const STATUS_UPLOADED = DocumentStatus::Uploaded->value;
    public const STATUS_PROCESSING = DocumentStatus::Processing->value;
    public const STATUS_REQUIRES_REVIEW = DocumentStatus::RequiresReview->value;
    public const STATUS_VERIFIED = DocumentStatus::Verified->value;
    public const STATUS_FAILED = DocumentStatus::Failed->value;
    public const STATUS_REJECTED = DocumentStatus::Rejected->value;

    protected $fillable = [
        'vendor_id',
        'project_id',
        'uploaded_by',
        'document_type',
        'document_number',
        'customer_name',
        'customer_company',
        'project_name',
        'project_location',
        'phone_no',
        'document_date',
        'original_filename',
        'original_mime_type',
        'companion_pdf_path',
        'disk_path',
        'file_hash',
        'status',
        'extraction_confidence',
        'failure_reason',
        'raw_extracted_text',
        'processed_at',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'document_date' => 'date',
            'processed_at' => 'datetime',
            'verified_at' => 'datetime',
            'extraction_confidence' => 'decimal:2',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(DocumentLineItem::class)->orderBy('line_no');
    }

    public function totals(): HasOne
    {
        return $this->hasOne(DocumentTotal::class);
    }

    public function transactionsAsQuotation(): HasMany
    {
        return $this->hasMany(Transaction::class, 'quotation_document_id');
    }

    public function transactionsAsPO(): HasMany
    {
        return $this->hasMany(Transaction::class, 'purchase_order_document_id');
    }

    public function transactionsAsOrderSlip(): HasMany
    {
        return $this->hasMany(Transaction::class, 'order_slip_document_id');
    }

    public function hasMismatches(): bool
    {
        if ($this->totals && ($this->totals->vat_mismatch || $this->totals->total_mismatch || $this->totals->subtotal_mismatch)) {
            return true;
        }

        return $this->lineItems()->where('total_mismatch', true)->exists();
    }

    public function getAbsolutePath(): ?string
    {
        if (empty($this->disk_path)) {
            return null;
        }

        if (file_exists($this->disk_path)) {
            return $this->disk_path;
        }

        $candidates = [
            storage_path('app/private/' . $this->disk_path),
            storage_path('app/' . $this->disk_path),
            storage_path('app/public/' . $this->disk_path),
            public_path('storage/' . $this->disk_path),
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($this->disk_path)) {
            return \Illuminate\Support\Facades\Storage::disk('local')->path($this->disk_path);
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->disk_path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->path($this->disk_path);
        }

        return null;
    }
}
