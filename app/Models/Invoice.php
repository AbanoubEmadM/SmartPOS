<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory;
    protected $guarded = ['id'];

    public function order()
    {
        return $this->hasOne(Order::class, 'invoice_id');
    }}
