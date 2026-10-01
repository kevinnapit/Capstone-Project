<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'customer_id', 'channel_id', 'created_by', 'status', 'ordered_at',
        'subtotal', 'discount_amount', 'grand_total', 'paid_amount', 'notes',
        'completed_at', 'cancelled_at',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'ordered_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(OrderChannel::class, 'channel_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->oldest('created_at');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest('paid_at');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(OrderAdjustment::class)->latest('created_at');
    }

    public function balanceDue(): float
    {
        return max(0, round((float) $this->grand_total - (float) $this->paid_amount, 2));
    }

    public function paymentStatus(): string
    {
        if ((float) $this->paid_amount <= 0) {
            return 'unpaid';
        }

        return $this->balanceDue() > 0 ? 'partial' : 'paid';
    }
}
