<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'product_name',
        'product_desc',
        'product_img',
        'category_id',
        'is_available',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public function category(): BelongsTo{
        return $this->belongsTo(Category::class);
    }
    public function variants(): HasMany{
        return $this->hasMany(ProductVariant::class, 'product_id');
    }
}
