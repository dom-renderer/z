<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function production()
    {
        return $this->belongsTo(Production::class, 'production_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductionProduct::class, 'product_id');
    }

    public function unit()
    {
        return $this->belongsTo(ProductionUom::class, 'unit_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
