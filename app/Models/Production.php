<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Production extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(ProductionItem::class, 'production_id');
    }

    public function logs()
    {
        return $this->hasMany(ProductionLog::class, 'production_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}
