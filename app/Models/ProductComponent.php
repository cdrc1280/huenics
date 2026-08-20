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
    ];

    protected function casts(): array
    {
        return [
            'additional_cost' => 'decimal:2',
            'is_default'      => 'boolean',
        ];
    }

    public function parentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'parent_product_id');
    }

    public function componentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'component_product_id');
    }
}
