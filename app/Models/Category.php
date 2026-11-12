<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use BelongsToBusiness;

    protected $fillable = ['business_id', 'name', 'description', 'photo_path', 'is_active'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
