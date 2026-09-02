<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Http\Requests\OrderStoreRequest;
use App\Jobs\SendSmsJob;
use App\Models\CombinedOrder;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ShippingCharge;
use App\Models\User;
use App\Utility\NotificationUtility;
use Illuminate\Support\Facades\Hash;
use App\Models\Coupon;
use DB;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Services\TelegramService;

class CustomOrderController extends Controller
{

    public function store(OrderStoreRequest $request, TelegramService $telegramService)
    {

        try {
            DB::beginTransaction();
            
            $user = auth('sanctum')->user() ?? null;

            if ($user == null) {
                $user = User::where('phone', $request->customer_address['phone'])->first();
                if ($user != null) {
                    $user = $user;
                } else {
                    $user = User::create([
                        'name' => $request->customer_address['name'],
                        'email' => $request->customer_address['email'] ?? null,
                        'phone' => $request->customer_address['phone'],
                        'password' => Hash::make(random_int(10000000, 99999999))
                    ]);

                    /* $sms = 'Your temporary password is ' . $user->password;
                    SendSmsJob::dispatch($sms, $user->phone); */
                }
            }

            //start insert customer address
            $customerAddress = [];
            if ($request->customer_address != null) {
                $customerAddress = [
                    'name'      => $request->customer_address['name'],
                    'email'     => $request->customer_address['email'] ?? null,
                    'phone'     => $request->customer_address['phone'],
                    'address'   => $request->customer_address['address'],
                    'comment'   => $request->customer_address['comment']
                ];
            }

            //start insert shipping address
            $shippingAddress = [];
            if ($request->shipping_address != null) {
                $shippingAddress = [
                    'name'      => $request->shipping_address['name'],
                    'email'     => $request->shipping_address['email'] ?? null,
                    'phone'     => $request->shipping_address['phone'],
                    'address'   => $request->shipping_address['address'],
                    'comment'   => $request->shipping_address['comment']
                ];
            }

            if ($user != null) {
                $combined_order = new CombinedOrder();
                $combined_order->user_id = $user->id ?? null;
                $combined_order->shipping_address = json_encode($shippingAddress);
                $combined_order->save();
            }

            //end insert shipping address

            $order = new Order;
            $order->combined_order_id = $combined_order->id ?? null;
            $order->user_id = $user->id ?? null;
            $order->shipping_address = json_encode($shippingAddress);
            $order->customer_address = json_encode($customerAddress);
            $order->shipping_type = $request->shipping_type;
            $order->pickup_point_id = $request->shipping_type == 'pickup_point' ? $request->pickup_point : 0;
            $order->order_from = 'web';
            $order->payment_type = $request->payment_type;
            $order->delivery_viewed = '0';
            $order->payment_status_viewed = '0';
            $order->code = date('YmdHis') . rand(100, 999);
            $order->date = strtotime('now');
            $order->payment_status = 'unpaid';
            $order->terms_accepted = $request->terms_accepted ? 1 : 0;
            $order->save();

            $subTotal = 0;
            $totalDiscount = 0;
            $totalTax = 0;
            $totalShippingCost = 0;
            $coupon_discount_amount = 0;

            foreach ($request->products as $key => $product) {
                $productDetail = Product::find($product['product_id']);

                if ($productDetail->current_stock < $product['quantity']) {
                    return response()->json(['status' => false, 'code' => 400, 'message' => translate('Product quantity is not available')]);
                }

                if ($productDetail->upcoming == 1) {
                    return response()->json(['status' => false, 'code' => 400, 'message' => translate('Product is not available')]);
                }

                $productDetail->current_stock = $productDetail->current_stock - $product['quantity'];
                $productDetail->num_of_sale = $productDetail->num_of_sale + $product['quantity'];
                $productDetail->save();

                //start check product variation
                if (isset($product['variant']) && $product['variant'] != null) {
                    $product_variation = $product['variant'];
                } else {
                    $product_variation = '';
                }

                $product_stock = ProductStock::where('product_id', $product['product_id'])->where('variant', $product_variation)->first();

                if ($productDetail->digital != 1 && $product['quantity'] > $product_stock->qty) {
                    $order->delete();
                    if ($combined_order != null)
                    {
                        $combined_order->delete();
                    }
                    return response()->json(['combined_order_id' => 0, 'status' => false, 'message' => translate('The requested quantity is not available for ') . $productDetail->name]);
                } elseif ($productDetail->digital != 1) {
                    $product_stock->qty -= $product['quantity'];
                    $product_stock->save();
                }
                //end check product variation

                $quantity = $product['quantity'];

                $total = $product_stock->price * $quantity;
                $subTotal += $total;
                
                if($product_stock->discount != null || $product_stock->discount > 0){
                    $discount = ($product_stock->price - $product_stock->discount) * $quantity;
                }elseif($productDetail->discount>0 && $productDetail->discount_type == 'percent'){
                     $discount = ($product_stock->price * $productDetail->discount) / 100 * $quantity;
                }else{
                    $discount = $productDetail->discount * $quantity;
                }
                $totalDiscount += $discount;

                $tax = $productDetail->tax * $quantity;
                $totalTax += $tax;

                $shipping = $productDetail->shipping_cost * $quantity;
                $totalShippingCost += $shipping;

                $thisProsuctTotal = $total + $tax + $shipping - $discount;

                $orderDetail = new OrderDetail;
                $orderDetail->order_id = $order->id;
                $orderDetail->seller_id = $productDetail->user_id;
                $orderDetail->product_id = $product['product_id'];
                $orderDetail->variation = $product_variation;
                $orderDetail->quantity = $quantity;
                $orderDetail->unit_price = ceil($thisProsuctTotal / $quantity);
                $orderDetail->price = $thisProsuctTotal;
                $orderDetail->tax = $tax;
                $orderDetail->shipping_cost = $shipping;
                $orderDetail->save();

                if ($productDetail->added_by == 'seller' && $productDetail->user->seller != null) {
                    $seller = $productDetail->user->seller;
                    $seller->num_of_sale += $quantity;
                    $seller->save();
                }
            }

            $shipping_cost = ShippingCharge::find($request->shipping_charge_id)->cost;
            $order->shipping_charge_id = $request->shipping_charge_id;
            $order->shipping_cost = $shipping_cost;

            //start coupon
            if ($request->filled('coupon_code')) {

                $coupon = Coupon::where('code', $request->coupon_code)
                    ->where('status', 1)
                    ->where('start_date', '<=', strtotime(date('d-m-Y')))
                    ->where('end_date', '>=', strtotime(date('d-m-Y')))
                    ->first();

                if ($coupon) {

                    $details = json_decode($coupon->details, true);

                    $coupon_code          = $coupon->code;
                    $coupon_discount      = (float) $coupon->discount;
                    $coupon_discount_type = $coupon->discount_type;
                    $coupon_min_buy       = (int) ($details['min_buy'] ?? 0);
                    $coupon_max_discount  = (int) ($details['max_discount'] ?? 0);

                    // minimum buy check
                    if ($subTotal >= $coupon_min_buy) {

                        switch ($coupon_discount_type) {
                            case 'percent':
                                $coupon_discount_amount = ($subTotal * $coupon_discount) / 100;
                                if ($coupon_max_discount > 0) {
                                    $discount_amount = min($coupon_discount_amount, $coupon_max_discount);
                                }
                                break;

                            case 'amount':
                                $coupon_discount_amount = $coupon_discount;
                                
                                break;

                            default:
                                $coupon_discount_amount = 0;
                        }

                        $order->coupon_code     = $coupon_code;
                        $order->coupon_discount = $coupon_discount_amount;
                    }
                }
            }
            //end coupon

            $add_total = ($subTotal + $totalTax + $totalShippingCost + $shipping_cost);
            $sub_total = ($totalDiscount + $coupon_discount_amount);
            $order->grand_total = $add_total - $sub_total;

            $order->save();

            DB::commit();

            try{
                $telegramService->sendMessage("New order placed. Order code: {$order->code}.");
            }catch(\Exception $e){
                Log::info($e->getMessage());
            }

            try{
                $sms = "Your order has been placed successfully.\n" . "Order code: {$order->code}.\n" . "https://agrony.com";
                SendSmsJob::dispatch($sms, $user->phone);
            }catch(\Exception $e){
                Log::info($e->getMessage());
            }

            if (auth('sanctum')->user()){
                NotificationUtility::sendOrderPlacedNotification($order);
            }

            return response()->json(['order_id' => $order->code, 'status' => true, 'code' => 200, 'message' => translate('Your order has been placed successfully')]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'code' => 400, 'message' => $e->getMessage()]);
        }
    }
}
