<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ExpiryMaterial extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function getProductIdAttribute()
    {
        $productId = $this->getAttributes()['product_id'];

        return DB::table('products')->select('name')->where('id', $productId)->first()->name ?? $productId;
    }

    public function getCategoryIdAttribute()
    {
        $categoryId = $this->getAttributes()['category_id'];

        return DB::table('product_categories')->select('name')->where('id', $categoryId)->first()->name ?? $categoryId;
    }
}
