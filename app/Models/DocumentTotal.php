<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTotal extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'printed_subtotal',
        'printed_vat',
        'printed_total',
        'computed_subtotal',
        'computed_vat',
        'computed_grand_total',
        'negotiated_amount',
        'subtotal_mismatch',
        'vat_mismatch',
        'total_mismatch',
    ];

    protected function casts(): array
    {
        return [
            'printed_subtotal' => 'decimal:2',
            'printed_vat' => 'decimal:2',
            'printed_total' => 'decimal:2',
            'computed_subtotal' => 'decimal:2',
            'computed_vat' => 'decimal:2',
            'computed_grand_total' => 'decimal:2',
            'negotiated_amount' => 'decimal:2',
            'subtotal_mismatch' => 'boolean',
            'vat_mismatch' => 'boolean',
            'total_mismatch' => 'boolean',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
