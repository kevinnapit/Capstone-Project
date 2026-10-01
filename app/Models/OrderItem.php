<?php

namespace App\Models;

use App\Enums\OrderItemType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'type', 'product_id', 'service_price_id', 'item_name_snapshot',
        'quantity_billed', 'unit_price_snapshot', 'subtotal', 'notes',
    ];

    protected $casts = [
        'type' => OrderItemType::class,
        'quantity_billed' => 'decimal:2',
        'unit_price_snapshot' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function servicePrice(): BelongsTo
    {
        return $this->belongsTo(ServicePrice::class);
    }

    public function serviceDetail(): HasOne
    {
        return $this->hasOne(ServiceItemDetail::class);
    }
}
