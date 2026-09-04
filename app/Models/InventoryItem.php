<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\LogsActivity;

    protected $fillable = [
        'product_id',
        'quantity_on_hand',
        'quantity_reserved',
        'reorder_point',
        'unit',
        'location',
        'supplier_name',
        'po_number',
        'customer_name',
        'project_name',
        'date_released',
        'inbound_date',
        'remarks',
        'last_counted_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity_on_hand'  => 'decimal:4',
            'quantity_reserved' => 'decimal:4',
            'reorder_point'     => 'decimal:4',
            'date_released'     => 'date',
            'inbound_date'      => 'date',
            'last_counted_at'   => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'inventory_item_id');
    }

    // Alias for compatibility
    public function transactions(): HasMany
    {
        return $this->movements();
    }

    public function getQuantityAvailableAttribute(): float
    {
        return (float) ($this->quantity_on_hand - $this->quantity_reserved);
    }
}
