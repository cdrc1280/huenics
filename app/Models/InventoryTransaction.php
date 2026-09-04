<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    use HasFactory, \App\Traits\LogsActivity;

    public $timestamps = false;

    protected $fillable = [
        'inventory_item_id',
        'transaction_type',
        'reference_type',
        'reference_id',
        'quantity',
        'po_number',
        'supplier_name',
        'customer_name',
        'project_name',
        'location',
        'date_released',
        'transit_in',
        'transit_out',
        'balance_after',
        'notes',
        'performed_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity'      => 'decimal:4',
            'transit_in'    => 'decimal:4',
            'transit_out'   => 'decimal:4',
            'balance_after' => 'decimal:4',
            'date_released' => 'date',
            'created_at'    => 'datetime',
        ];
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
