<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Order;
use App\Models\Upload;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use App\Models\Cart;
use Hash;
use Illuminate\Support\Facades\File;
use Storage;

class ProfileController extends Controller
{
    public function details()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => translate("User not found.")
            ]);
        }
        return response()->json([
            'user' => [
                'id'                    => $user->id,
                'name'                  => $user->name,
                'email'                 => $user->email,
                'phone'                 => $user->phone,
                'avatar'                => $user->avatar,
                'address'               => $user->address,
                'country'               => $user->country,
                'state'                 => $user->state,
                'district'              => $user->district->name ?? null,
                'district_id'           => $user->district_id ?? null,
                'upazila'               => $user->upazila->name ?? null,
                'upazila_id'            => $user->upazila_id ?? null,
                'avatar_original'       => $user->avatar_original,
                'cart_item_count'       => Cart::where('user_id', auth()->user()->id)->count(),
                'wishlist_item_count'   => Wishlist::where('user_id', auth()->user()->id)->count(),
                'order_count'           => Order::where('user_id', auth()->user()->id)->count(),
            ],
        ]);
    }

    public function counters()
    {
        return response()->json([
            'cart_item_count' => Cart::where('user_id', auth()->user()->id)->count(),
            'wishlist_item_count' => Wishlist::where('user_id', auth()->user()->id)->count(),
            'order_count' => Order::where('user_id', auth()->user()->id)->count(),
        ]);
    }

    public function update(Request $request)
    {
        $user = User::find(auth()->user()->id);
        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => translate("User not found.")
            ]);
        }

        if (isset($request->name)) {
            $user->name = $request->name;
        }

        if (isset($request->address)) {
            $user->address = $request->address;
        }

        if (isset($request->district_id)) {
            $user->district_id = $request->district_id;
        }

        if (isset($request->upazila_id)) {
            $user->upazila_id = $request->upazila_id;
        }

        if (isset($request->password)) {
            if ($request->password != "") {
                $user->password = Hash::make($request->password);
            }
        }
        $user->save();

        return response()->json([
            'result' => true,
            'message' => translate("Profile information updated")
        ]);
    }

    public function update_device_token(Request $request)
    {
        $user = User::find(auth()->user()->id);
        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => translate("User not found.")
            ]);
        }

        $user->device_token = $request->device_token;


        $user->save();

        return response()->json([
            'result' => true,
            'message' => translate("device token updated")
        ]);
    }

    public function updateImage(Request $request)
    {
        $user = User::find(auth()->user()->id);
        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => translate("User not found."),
                'path' => ""
            ]);
        }

        $type = array( "jpg" => "image", "jpeg" => "image", "png" => "image", "svg" => "image", "webp" => "image", "gif" => "image");

        try {
            $image = $request->image;
            $request->filename;
            $realImage = base64_decode($image);

            $dir = public_path('uploads/all');
            $full_path = "$dir/$request->filename";

            $file_put = file_put_contents($full_path, $realImage); // int or false

            if ($file_put == false) {
                return response()->json([
                    'result' => false,
                    'message' => "File uploading error",
                    'path' => ""
                ]);
            }


            $upload = new Upload;
            $extension = strtolower(File::extension($full_path));
            $size = File::size($full_path);

            if (!isset($type[$extension])) {
                unlink($full_path);
                return response()->json([
                    'result' => false,
                    'message' => "Only image can be uploaded",
                    'path' => ""
                ]);
            }


            $upload->file_original_name = null;
            $arr = explode('.', File::name($full_path));
            for ($i = 0; $i < count($arr) - 1; $i++) {
                if ($i == 0) {
                    $upload->file_original_name .= $arr[$i];
                } else {
                    $upload->file_original_name .= "." . $arr[$i];
                }
            }

            //unlink and upload again with new name
            unlink($full_path);
            $newFileName = rand(10000000000, 9999999999) . date("YmdHis") . "." . $extension;
            $newFullPath = "$dir/$newFileName";

            $file_put = file_put_contents($newFullPath, $realImage);

            if ($file_put == false) {
                return response()->json([
                    'result' => false,
                    'message' => "Uploading error",
                    'path' => ""
                ]);
            }

            $newPath = "uploads/all/$newFileName";

            if (env('FILESYSTEM_DRIVER') == 's3') {
                Storage::disk('s3')->put(
                    $newPath,
                    file_get_contents(base_path('public/') . $newPath),
                    ['visibility' => 'public']
                );
                unlink(base_path('public/') . $newPath);
            }

            $upload->extension = $extension;
            $upload->file_name = $newPath;
            $upload->user_id = $user->id;
            $upload->type = $type[$upload->extension];
            $upload->file_size = $size;
            $upload->save();

            $user->avatar = $upload->file_name;
            $user->avatar_original = $upload->id;
            $user->save();

            return response()->json([
                'result' => true,
                'message' => translate("Image updated"),
                'path' => uploaded_asset($upload->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
                'path' => ""
            ]);
        }
    }

    // not user profile image but any other base 64 image through uploader
    public function imageUpload(Request $request)
    {
        $user = User::find(auth()->user()->id);
        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => translate("User not found."),
                'path' => "",
                'upload_id' => 0
            ]);
        }

        $type = array(
            "jpg" => "image",
            "jpeg" => "image",
            "png" => "image",
            "svg" => "image",
            "webp" => "image",
            "gif" => "image",
        );

        try {
            $image = $request->image;
            $request->filename;
            $realImage = base64_decode($image);

            $dir = public_path('uploads/all');
            $full_path = "$dir/$request->filename";

            $file_put = file_put_contents($full_path, $realImage); // int or false

            if ($file_put == false) {
                return response()->json([
                    'result' => false,
                    'message' => "File uploading error",
                    'path' => "",
                    'upload_id' => 0
                ]);
            }


            $upload = new Upload;
            $extension = strtolower(File::extension($full_path));
            $size = File::size($full_path);

            if (!isset($type[$extension])) {
                unlink($full_path);
                return response()->json([
                    'result' => false,
                    'message' => "Only image can be uploaded",
                    'path' => "",
                    'upload_id' => 0
                ]);
            }


            $upload->file_original_name = null;
            $arr = explode('.', File::name($full_path));
            for ($i = 0; $i < count($arr) - 1; $i++) {
                if ($i == 0) {
                    $upload->file_original_name .= $arr[$i];
                } else {
                    $upload->file_original_name .= "." . $arr[$i];
                }
            }

            //unlink and upload again with new name
            unlink($full_path);
            $newFileName = rand(10000000000, 9999999999) . date("YmdHis") . "." . $extension;
            $newFullPath = "$dir/$newFileName";

            $file_put = file_put_contents($newFullPath, $realImage);

            if ($file_put == false) {
                return response()->json([
                    'result' => false,
                    'message' => "Uploading error",
                    'path' => "",
                    'upload_id' => 0
                ]);
            }

            $newPath = "uploads/all/$newFileName";

            if (env('FILESYSTEM_DRIVER') == 's3') {
                Storage::disk('s3')->put($newPath, file_get_contents(base_path('public/') . $newPath));
                unlink(base_path('public/') . $newPath);
            }

            $upload->extension = $extension;
            $upload->file_name = $newPath;
            $upload->user_id = $user->id;
            $upload->type = $type[$upload->extension];
            $upload->file_size = $size;
            $upload->save();

            return response()->json([
                'result' => true,
                'message' => translate("Image updated"),
                'path' => uploaded_asset($upload->id),
                'upload_id' => $upload->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
                'path' => "",
                'upload_id' => 0
            ]);
        }
    }

    public function checkIfPhoneAndEmailAvailable()
    {
        $phone_available = false;
        $email_available = false;
        $phone_available_message = translate("User phone number not found");
        $email_available_message = translate("User email  not found");

        $user = User::find(auth()->user()->id);

        if ($user->phone != null || $user->phone != "") {
            $phone_available = true;
            $phone_available_message = translate("User phone number found");
        }

        if ($user->email != null || $user->email != "") {
            $email_available = true;
            $email_available_message = translate("User email found");
        }
        return response()->json(
            [
                'phone_available' => $phone_available,
                'email_available' => $email_available,
                'phone_available_message' => $phone_available_message,
                'email_available_message' => $email_available_message,
            ]
        );
    }

    public function orders(Request $request)
    {
        $user = auth()->user();
        $orders = Order::query()
        ->with([
            'orderDetails:id,seller_id,order_id,product_id,variation,price,unit_price,tax,quantity',
            'orderDetails.product:id,slug,name,thumbnail_img',
        ])
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc');

        if ($request->has('status') && $request->status != null) {
            $orders = $orders->where('delivery_status', $request->status);
        }

        $orders = $orders->paginate(10);

        // Process relations AFTER pagination
        $orders->getCollection()->transform(function ($order) {
            $order->orderDetails->transform(function ($orderDetail) {
                if ($orderDetail->product) {
                    $orderDetail->total = $orderDetail->price;
                    $orderDetail->price = $orderDetail->unit_price;
                    $orderDetail->product_name      = $orderDetail->product->name;
                    $orderDetail->product_thumbnail = uploaded_asset($orderDetail->product->thumbnail_img);
                    unset($orderDetail->product);
                }
                return $orderDetail;
            });
            return $order;
        });

        $ordersdata = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'code' => $order->code,
                'customer_address' => json_decode($order->customer_address),
                'shipping_address' => json_decode($order->shipping_address),
                'payment_type' => $order->payment_type,
                'payment_status' => $order->payment_status,
                'delivery_status' => $order->delivery_status,
                'order_details' => $order->orderDetails,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at
            ];
        });

        return response()->json(['code' => 200, 'status' => 'success', 'orders' => $ordersdata, 'message' => "Orders fetched successfully"]);
    }

    public function order($code)
    {
        $order = Order::with([
            'orderDetails:id,seller_id,order_id,product_id,variation,price,unit_price,tax,quantity',
            'orderDetails.product:id,slug,name,thumbnail_img',
        ])
        ->where('code', $code)
        ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->orderDetails->map(function ($orderDetail) {
            //evabe code korar kron frontend smoy pay nai tai frontend er variable onuzai kaj korte emon korte holo.
            $orderDetail->total = $orderDetail->price;
            $orderDetail->price = $orderDetail->unit_price;
            $orderDetail->product_name = $orderDetail->product->name;
            $orderDetail->product_slug = $orderDetail->product->slug;
            $orderDetail->product_thumbnail = uploaded_asset($orderDetail->product->thumbnail_img);
            unset($orderDetail->product);
            return $orderDetail;
        });
            
        $orderData = [
            'id' => $order->id,
            'code' => $order->code,
            'customer_address' => json_decode($order->customer_address),
            'shipping_address' => json_decode($order->shipping_address),
            'payment_type' => $order->payment_type,
            'payment_status' => $order->payment_status,
            'delivery_status' => $order->delivery_status,
            'order_details' => $order->orderDetails,
            'shipping_cost' => $order->shipping_cost,
            'coupon_discount' => $order->coupon_discount,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at
        ];

        return response()->json($orderData);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|confirmed|min:8',
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => translate("User not found.")
            ]);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'result' => false,
                'message' => translate("Current password is incorrect.")
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'result' => true,
            'message' => translate("Password updated")
        ]);
    }

}
