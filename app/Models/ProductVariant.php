<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    /** @use HasFactory<\Database\Factories\ProductVariantFactory> */
    use HasFactory;
    protected $guarded= ['variant_id'];

    public function orderItems(): HasMany{
        return $this->hasMany(OrderItem::class);
    }
    public function product(): BelongsTo{
        return $this->belongsTo(Product::class);
    }
}
