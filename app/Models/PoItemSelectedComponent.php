<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoItemSelectedComponent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'po_line_item_id',
        'component_id',
        'component_group',
        'selected_option_name',
        'unit_cost',
        'total_cost',
        'is_deducted_from_inventory',
    ];

    protected function casts(): array
    {
        return [
            'unit_cost'                   => 'decimal:2',
            'total_cost'                  => 'decimal:2',
            'is_deducted_from_inventory'  => 'boolean',
            'created_at'                  => 'datetime',
        ];
    }

    public function lineItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderLineItem::class, 'po_line_item_id');
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(ProductComponent::class, 'component_id');
    }
}
