<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id', 'branch_id', 'warehouse_id', 'movement_date', 'source', 'cost', 'proof_path',
        'product_id',
        'user_id',
        'type',
        'qty',
        'stock_after',
        'reference',
        'note',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
