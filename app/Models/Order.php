<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;
    protected $guarded= ['order_id'];
    public function customer(): BelongsTo {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function items(): HasMany {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
    public function employee(): BelongsTo {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function invoice(): BelongsTo {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
