<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceItemDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id', 'paper_type_id', 'pages', 'copies', 'sheets_billed', 'sheets_consumed',
    ];

    protected $casts = [
        'pages' => 'integer',
        'copies' => 'integer',
        'sheets_billed' => 'integer',
        'sheets_consumed' => 'integer',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function paperType(): BelongsTo
    {
        return $this->belongsTo(PaperType::class);
    }
}
