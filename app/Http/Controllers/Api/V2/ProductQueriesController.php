<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Product;
use App\Models\ProductQuery;
use Illuminate\Http\Request;

class ProductQueriesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:view_all_product_questions'])->only('admin_index');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'question' => 'required|string',
        ]);

        $product = Product::find($request->product_id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => __('Product not found'),
            ], 404);
        }

        $query = new ProductQuery();
        $query->customer_id = auth('sanctum')->user()->id;
        $query->seller_id = $product->user_id;
        $query->product_id = $product->id;
        $query->question = $request->question;
        $query->save();

        $query->makeHidden(['created_at', 'updated_at', 'seller_id']);

        return response()->json([
            'success' => true,
            'message' => __('Product question has been submitted successfully'),
            'query' => $query,
        ], 200);
    }

}
