<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class WishlistCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                /* return [
                    'id' => (integer) $data->id,
                    'product' => [
                        'id' => $data->product->id,
                        'name' => $data->product->name,
                        'slug' => $data->product->slug,
                        'thumbnail_image' => uploaded_asset($data->product->thumbnail_img),
                        'base_price' => format_price(home_base_price($data->product, false)) ,
                        'rating' => (double) $data->product->rating,
                    ]
                ]; */
                return [
                    'id' => $data->product->id,
                    'name' => $data->product->name,
                    'slug' => $data->product->slug,
                    'price' => single_price($data->product->unit_price),
                    'current_stock' => $data->product->current_stock,
                    'model' => $data->product->model, 
                    'discount' => $data->product->discount,
                    'thumbnail_image' => uploaded_asset($data->product->thumbnail_img),
                    'base_price' => format_price(home_base_price($data->product, false)) ,
                    'rating' => (double) $data->product->rating,
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
