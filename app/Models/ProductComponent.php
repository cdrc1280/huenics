<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_product_id',
        'component_group',
        'option_name',
        'component_product_id',
        'additional_cost',
        'is_default',
        'quantity',
        'product_code',
        'component_name',
        'category',
        'wattage',
        'voltage',
        'color_temperature',
        'unit',
        'cost_price',
        'image_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'additional_cost' => 'decimal:2',
            'cost_price'      => 'decimal:2',
            'quantity'        => 'decimal:4',
            'is_default'      => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ProductComponent $component) {
            if (empty($component->component_group)) {
                $component->component_group = $component->category ?: ($component->componentProduct?->category ?? 'General');
            }
            if (empty($component->option_name)) {
                $component->option_name = $component->component_name ?: ($component->componentProduct?->canonical_name ?? 'Part');
            }
            if ((float) $component->cost_price > 0 && (float) $component->additional_cost <= 0) {
                $component->additional_cost = $component->cost_price;
            }
        });

        static::saved(function (ProductComponent $component) {
            if ($component->component_product_id) {
                \App\Models\InventoryItem::firstOrCreate(
                    ['product_id' => $component->component_product_id],
                    [
                        'quantity_on_hand' => 0,
                        'quantity_reserved' => 0,
                        'reorder_point' => 10,
                        'unit' => $component->effective_unit ?: 'pcs',
                        'is_owned' => true,
                    ]
                );
            }
        });
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function parentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'parent_product_id');
    }

    public function componentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'component_product_id');
    }

    // ─── Dynamic Fallback Accessors (Matching Product Schema) ─────────

    public function getEffectiveCodeAttribute(): string
    {
        return $this->product_code ?: ($this->componentProduct?->product_code ?? '—');
    }

    public function getEffectiveNameAttribute(): string
    {
        return $this->component_name ?: ($this->option_name ?: ($this->componentProduct?->canonical_name ?? '—'));
    }

    public function getEffectiveCategoryAttribute(): string
    {
        return $this->category ?: ($this->component_group ?: ($this->componentProduct?->category ?? 'General'));
    }

    public function getEffectiveWattageAttribute(): ?string
    {
        return $this->wattage ?: $this->componentProduct?->wattage;
    }

    public function getEffectiveVoltageAttribute(): ?string
    {
        return $this->voltage ?: $this->componentProduct?->voltage;
    }

    public function getEffectiveColorAttribute(): ?string
    {
        return $this->color_temperature ?: $this->componentProduct?->color_temperature;
    }

    public function getEffectiveUnitAttribute(): string
    {
        return $this->unit ?: ($this->componentProduct?->unit_default ?? 'pcs');
    }

    public function getEffectiveCostAttribute(): float
    {
        if ((float) $this->cost_price > 0) {
            return (float) $this->cost_price;
        }

        if ((float) $this->additional_cost > 0) {
            return (float) $this->additional_cost;
        }

        return (float) ($this->componentProduct?->base_cost_price ?? $this->componentProduct?->selling_price ?? 0.0);
    }

    public function getEffectiveImageAttribute(): ?string
    {
        return $this->image_path ?: $this->componentProduct?->image_path;
    }

    public function getStockOnHandAttribute(): ?float
    {
        return $this->componentProduct?->inventoryItem?->quantity_on_hand !== null
            ? (float) $this->componentProduct->inventoryItem->quantity_on_hand
            : null;
    }

    public function getTotalCostAttribute(): float
    {
        $unitCost = $this->effective_cost;
        $qty = (float) ($this->quantity ?: 1);

        return round($unitCost * $qty, 2);
    }
}
