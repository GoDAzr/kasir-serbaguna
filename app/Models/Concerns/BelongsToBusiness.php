<?php

namespace App\Models\Concerns;

use App\Support\Tenant;
use Illuminate\Database\Eloquent\Builder;

/** Membatasi model ke usaha milik user yang login dan mengisi business_id otomatis. */
trait BelongsToBusiness
{
    protected static function bootBelongsToBusiness(): void
    {
        static::addGlobalScope('business', function (Builder $q) {
            if ($id = Tenant::id()) {
                $q->where($q->getModel()->getTable() . '.business_id', $id);
            }
        });

        static::creating(function ($model) {
            if (empty($model->business_id) && ($id = Tenant::id())) {
                $model->business_id = $id;
            }
        });
    }
}
