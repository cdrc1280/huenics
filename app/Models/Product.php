<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\LogsActivity;

    protected $fillable = [
        'product_code',
        'canonical_name',
        'description',
        'image_path',
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

    /**
     * Get the active display price for customer facing views.
     */
    public function getDisplayPriceAttribute(): float
    {
        $selling = (float) $this->selling_price;
        if ($selling > 0) {
            return $selling;
        }

        $default = (float) $this->default_price;
        if ($default > 0) {
            return $default;
        }

        return 0.0;
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

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        if (file_exists(public_path($this->image_path))) {
            return asset($this->image_path);
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->image_path);
    }

    public function getBase64ImageAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        $fullPath = null;
        if (file_exists($this->image_path)) {
            $fullPath = $this->image_path;
        } elseif (file_exists(public_path($this->image_path))) {
            $fullPath = public_path($this->image_path);
        } elseif (file_exists(public_path('storage/' . $this->image_path))) {
            $fullPath = public_path('storage/' . $this->image_path);
        } elseif (file_exists(storage_path('app/public/' . $this->image_path))) {
            $fullPath = storage_path('app/public/' . $this->image_path);
        } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->image_path)) {
            $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($this->image_path);
        }

        if ($fullPath && file_exists($fullPath)) {
            $type = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            if ($type === 'jpg') $type = 'jpeg';
            $data = @file_get_contents($fullPath);
            if ($data !== false && !empty($data)) {
                return 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        return null;
    }

    public static function getSkuOptions(): array
    {
        return static::query()
            ->whereNotNull('sku')
            ->where('sku', '!=', '')
            ->orderBy('sku')
            ->pluck('sku', 'sku')
            ->toArray();
    }
}
