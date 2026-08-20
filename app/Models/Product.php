<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_code',
        'canonical_name',
        'description',
        'sku',
        'category',
        'unit_default',
        'default_price',
        'base_cost_price',
        'selling_price',
        'is_huenics_owned',
        'is_composite',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_huenics_owned' => 'boolean',
            'is_composite'     => 'boolean',
            'is_active'        => 'boolean',
            'default_price'    => 'decimal:2',
            'base_cost_price'  => 'decimal:2',
            'selling_price'    => 'decimal:2',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function aliases(): HasMany
    {
        return $this->hasMany(ProductAlias::class);
    }

    public function inventoryItem(): HasOne
    {
        return $this->hasOne(InventoryItem::class);
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(DocumentLineItem::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(ProductComponent::class, 'parent_product_id');
    }

    public function componentGroups(): array
    {
        return $this->components()
            ->get()
            ->groupBy('component_group')
            ->map(fn ($items) => $items->values())
            ->toArray();
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    public function scopeHuenicsOwned($query)
    {
        return $query->where('is_huenics_owned', true);
    }

    public function scopeComposite($query)
    {
        return $query->where('is_composite', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Computed ─────────────────────────────────────────────────────

    public function getDisplayNameAttribute(): string
    {
        return $this->product_code
            ? "[{$this->product_code}] {$this->canonical_name}"
            : $this->canonical_name;
    }

    public function getGrossProfitMarginAttribute(): float
    {
        if ((float) $this->selling_price <= 0) {
            return 0.0;
        }
        return round((((float) $this->selling_price - (float) $this->base_cost_price) / (float) $this->selling_price) * 100, 1);
    }
}
