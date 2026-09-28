<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'brand_id',
        'package_unit_id',
        'sku',
        'name',
        'package_size',
        'selling_price',
        'low_stock_threshold',
        'manufacturer_code',
        'description',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'package_size' => 'decimal:3',
            'selling_price' => 'decimal:2',
            'low_stock_threshold' => 'decimal:3',
            'active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<Brand, $this> */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /** @return BelongsTo<PackageUnit, $this> */
    public function packageUnit(): BelongsTo
    {
        return $this->belongsTo(PackageUnit::class);
    }

    /** @return HasOne<Inventory, $this> */
    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

    /** @return HasMany<InventoryMovement, $this> */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
