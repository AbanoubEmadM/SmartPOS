<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    /** @use HasFactory<\Database\Factories\OrderItemFactory> */
    use HasFactory;
    protected $fillable = [
        'order_id',
        'variant_id',
        'product_name',
        'quantity',
        'current_price_cents',
    ];
    public function order(): BelongsTo {
        return $this->belongsTo(Order::class, 'order_id');
    }
    public function variant(): BelongsTo {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
