<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriberController extends Controller
{
   public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            $data = ['status' => false, 'message' => $validator->errors()->first()];
            return response()->json($data, 200);
        }

        $subscriber = Subscriber::where('email', $request->email)->first();
        if ($subscriber == null) {
            $subscriber = new Subscriber;
            $subscriber->email = $request->email;
            $subscriber->save();
            $data = ['status' => true, 'message' => 'Subscribed successfully'];
        } else {
            $data = ['status' => false, 'message' => 'You are already subscribed'];
        }
        
        return response()->json($data, 200);
    }

}
