<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'invoice_no',
        'client_uuid',
        'user_id',
        'customer_id',
        'subtotal',
        'discount',
        'tax',
        'total',
        'paid',
        'change',
        'payment_method',
        'status',
        'note',
        'voided_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'paid' => 'decimal:2',
            'change' => 'decimal:2',
            'voided_at' => 'datetime',
        ];
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
