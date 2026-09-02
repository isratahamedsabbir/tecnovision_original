<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\CouponCollection;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomCouponController extends Controller
{
    /* public function apply(Request $request)
    {
        $coupon = Coupon::where('code', $request->code)->first();

        if ($coupon != null && strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date && CouponUsage::where('user_id', auth()->user()->id)->where('coupon_id', $coupon->id)->first() == null) {
            $couponDetails = json_decode($coupon->details);
            if ($coupon->type == 'cart_base') {
                $sum = Cart::where('user_id', auth()->user()->id)->active()->sum('price');
                if ($sum > $couponDetails->min_buy) {
                    if ($coupon->discount_type == 'percent') {
                        $couponDiscount =  ($sum * $coupon->discount) / 100;
                        if ($couponDiscount > $couponDetails->max_discount) {
                            $couponDiscount = $couponDetails->max_discount;
                        }
                    } elseif ($coupon->discount_type == 'amount') {
                        $couponDiscount = $coupon->discount;
                    }
                    if ($this->isCouponAlreadyApplied(auth()->user()->id, $coupon->id)) {
                        return response()->json([
                            'success' => false,
                            'message' => translate('The coupon is already applied. Please try another coupon')
                        ]);
                    } else {
                        return response()->json([
                            'success' => true,
                            'discount' => (float) $couponDiscount
                        ]);
                    }
                }
            } elseif ($coupon->type == 'product_base') {
                $couponDiscount = 0;
                $cartItems = Cart::where('user_id', auth()->user()->id)->active()->get();
                foreach ($cartItems as $key => $cartItem) {
                    foreach ($couponDetails as $key => $couponDetail) {
                        if ($couponDetail->product_id == $cartItem->product_id) {
                            if ($coupon->discount_type == 'percent') {
                                $couponDiscount += $cartItem->price * $coupon->discount / 100;
                            } elseif ($coupon->discount_type == 'amount') {
                                $couponDiscount += $coupon->discount;
                            }
                        }
                    }
                }
                if ($this->isCouponAlreadyApplied(auth()->user()->id, $coupon->id)) {
                    return response()->json([
                        'success' => false,
                        'message' => translate('The coupon is already applied. Please try another coupon')
                    ]);
                } else {
                    return response()->json([
                        'success' => true,
                        'discount' => (float) $couponDiscount,
                        'message' => translate('Coupon code applied successfully')
                    ]);
                }
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => translate('The coupon is invalid')
            ]);
        }
    } */

    public function couponList()
    {
        $coupons = Coupon::where('start_date', '<=', strtotime(date('d-m-Y')))->where('end_date', '>=', strtotime(date('d-m-Y')))->paginate(10);
        return new CouponCollection($coupons);
    }

    public function couponCheck($code)
    {
        $coupon = Coupon::query()
        ->where('status', 1)
        ->where('code', $code)
        ->where('start_date', '<=', strtotime(date('d-m-Y')))
        ->where('end_date', '>=', strtotime(date('d-m-Y')))
        ->first();

        if (!$coupon) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
                'msg' => 'Invalid coupon code',
                'data' => []
            ], 404);
        }

        $coupon->details = json_decode($coupon->details, true);

        $data = [
            'id' => $coupon->id,
            "user_type" => $coupon->user->user_type,
            'coupon_type' => $coupon->type,
            'code' => $coupon->code,
            'discount' => $coupon->discount_type == 'percent' ? $coupon->discount : single_price($coupon->discount),
            'discount_type' => $coupon->discount_type,
            'coupon_discount_details' => $coupon->details ?? [],
            'start_date' => $coupon->start_date,
            'end_date' => $coupon->end_date,
        ];

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'msg' => 'Coupon details',
            'data' => $data
        ], 200);
    }


}
