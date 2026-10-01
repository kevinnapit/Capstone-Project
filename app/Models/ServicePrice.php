<?php

namespace App\Models;

use App\Enums\SideMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServicePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_type_id', 'paper_type_id', 'print_mode_id', 'side_mode', 'price',
        'effective_from', 'effective_until', 'is_active',
    ];

    protected $casts = [
        'side_mode' => SideMode::class,
        'price' => 'decimal:2',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function paperType(): BelongsTo
    {
        return $this->belongsTo(PaperType::class);
    }

    public function printMode(): BelongsTo
    {
        return $this->belongsTo(PrintMode::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
