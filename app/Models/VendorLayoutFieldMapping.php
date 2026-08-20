<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorLayoutFieldMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'layout_id',
        'field_key',
        'target_scope',
        'extraction_strategy',
        'regex_pattern',
        'column_start',
        'column_end',
        'row_offset',
        'post_process',
        'is_required',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'column_start' => 'integer',
            'column_end' => 'integer',
            'row_offset' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function layout(): BelongsTo
    {
        return $this->belongsTo(VendorDocumentLayout::class, 'layout_id');
    }
}
