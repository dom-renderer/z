<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionProductUom extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(ProductionProduct::class, 'product_id');
    }

    public function uom()
    {
        return $this->belongsTo(ProductionUom::class, 'uom_id');
    }
}
