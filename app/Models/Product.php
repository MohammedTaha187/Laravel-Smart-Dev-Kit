<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Traits\HasProfessionalLogs;
use App\Traits\HasPayments;
use App\Traits\BelongsToTenant;

class Product extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia, HasProfessionalLogs, HasPayments, BelongsToTenant;

    protected $fillable = ['name', 'price', 'description', 'tenant_id'];
}
