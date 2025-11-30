<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductionPlanning extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(ProductionProduct::class, 'product_id');
    }

    public function unit()
    {
        return $this->belongsTo(ProductionUom::class, 'uom_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }    
}
