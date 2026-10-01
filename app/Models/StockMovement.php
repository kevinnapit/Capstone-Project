<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['product_id', 'movement_type', 'direction', 'quantity', 'unit_cost', 'stock_before', 'stock_after', 'reference_type', 'reference_id', 'notes', 'created_by', 'occurred_at'];

    protected $casts = ['quantity' => 'decimal:2', 'unit_cost' => 'decimal:2', 'stock_before' => 'decimal:2', 'stock_after' => 'decimal:2', 'occurred_at' => 'datetime'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
