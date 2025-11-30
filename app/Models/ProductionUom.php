<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionUom extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function scopeActive($query) {
        return $query->where('status', 'active');
    }

    public function products()
    {
        return $this->belongsToMany(ProductionProduct::class, 'production_product_uoms', 'uom_id', 'product_id');
    }
}
