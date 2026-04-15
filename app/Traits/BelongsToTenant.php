<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    public static function bootBelongsToTenant()
    {
        static::creating(function ($model) {
            if (! $model->tenant_id && tenancy()->initialized) {
                $model->tenant_id = tenant('id');
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (tenancy()->initialized) {
                $builder->where('tenant_id', tenant('id'));
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
