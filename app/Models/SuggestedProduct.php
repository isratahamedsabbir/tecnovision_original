<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuggestedProduct extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'suggested_product_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'suggested_product_id');
    }
}
