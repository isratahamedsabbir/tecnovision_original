<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\ShippingCharge;
use Cache;

class ShippingChargeController extends Controller
{
   public function index()
    {
        if(Cache::has('shipping_charges')) {
            $shipping_charges = Cache::get('shipping_charges');
        }else{
            $shipping_charges = ShippingCharge::select('id', 'name', 'cost')->where('status', 1)->orderBy('id', 'asc')->get();
        }
        return response()->json([
            'result' => true,
            'message' => translate('Shipping Charges retrieved successfully'),
            'shipping_charges' => $shipping_charges
        ]);
    } 
}
