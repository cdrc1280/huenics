<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorDocumentLayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'document_type',
        'layout_version',
        'is_active',
        'header_identifier_regex',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'layout_version' => 'integer',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function fieldMappings(): HasMany
    {
        return $this->hasMany(VendorLayoutFieldMapping::class, 'layout_id')->orderBy('sort_order');
    }
}
