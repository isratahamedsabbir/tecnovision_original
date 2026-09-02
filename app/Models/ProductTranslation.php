<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;

class ProductTranslation extends Model
{
  use PreventDemoModeChanges;

    protected $fillable = ['product_id', 'name', 'unit', 'description', 'short_description', 'lang', 'specification', 'key_features'];

    public function product(){
      return $this->belongsTo(Product::class);
    }
}
