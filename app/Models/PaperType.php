<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaperType extends Model
{
    use HasFactory;

    protected $fillable = ['inventory_product_id', 'code', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function inventoryProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'inventory_product_id');
    }

    public function servicePrices(): HasMany
    {
        return $this->hasMany(ServicePrice::class);
    }

    public function serviceItemDetails(): HasMany
    {
        return $this->hasMany(ServiceItemDetail::class);
    }
}
