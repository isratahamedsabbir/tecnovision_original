<?php

namespace App\Http\Controllers\Api\V2;


use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\V2\ProductMiniCollection;
use App\Http\Resources\V2\ProductDetailCollection;
use App\Utility\SearchUtility;
use App\Utility\CategoryUtility;

class CustomProductController extends Controller
{
    public function show($slug, Request $request)
    {
        $product = Product::where('slug', $slug)->where('published', '1')->get();
        $user = $request->user('api');
        if($user != null){
            lastViewedProducts($product[0]->id, $user->id);
        }
        return new ProductDetailCollection(Product::where('slug', $slug)->get());
    }

    public function relatedProductsByCategory($slug)
    {
        $product = Product::where('slug', $slug)->first();
        if ($product) {
            $products = Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('published', '1')
                ->paginate(5);
            return new ProductMiniCollection($products);
        }
        return response()->json(['error' => 'Product not found'], 404);
    }

    public function relatedProductsByBrand($slug)
    {
        $product = Product::where('slug', $slug)->first();
        if ($product) {
            $products = Product::where('brand_id', $product->brand_id)
                ->where('id', '!=', $product->id)
                ->where('published', '1')
                ->paginate(5);
            return new ProductMiniCollection($products);
        }
        return response()->json(['error' => 'Product not found'], 404);
    }

    public function search(Request $request)
    {
        $category_ids = [];
        $brand_ids = [];

        if ($request->categories != null && $request->categories != "") {
            $category_ids = explode(',', $request->categories);
        }

        if ($request->brands != null && $request->brands != "") {
            $brand_ids = explode(',', $request->brands);
        }

        $sort_by = $request->sort_key;
        $name = $request->name;
        $min = $request->min;
        $max = $request->max;

        $products = Product::query();

        $products->where('published', 1)->physical();

        if (!empty($brand_ids)) {
            $products->whereIn('brand_id', $brand_ids);
        }

        if (!empty($category_ids)) {
            $n_cid = [];
            foreach ($category_ids as $cid) {
                $n_cid = array_merge($n_cid, CategoryUtility::children_ids($cid));
            }

            if (!empty($n_cid)) {
                $category_ids = array_merge($category_ids, $n_cid);
            }

            $products->whereIn('category_id', $category_ids);
        }

        if ($name != null && $name != "") {
            $products->where(function ($query) use ($name) {
                foreach (explode(' ', trim($name)) as $word) {
                    $query->where('name', 'like', '%' . $word . '%')->orWhere('tags', 'like', '%' . $word . '%')->orWhereHas('product_translations', function ($query) use ($word) {
                        $query->where('name', 'like', '%' . $word . '%');
                    });
                }
            });
            SearchUtility::store($name);
            $case1 = $name . '%';
            $case2 = '%' . $name . '%';

            $products->orderByRaw('CASE
                WHEN name LIKE "'.$case1.'" THEN 1
                WHEN name LIKE "'.$case2.'" THEN 2
                ELSE 3
                END');
        }

        if ($min != null && $min != "" && is_numeric($min)) {
            $products->where('unit_price', '>=', $min);
        }

        if ($max != null && $max != "" && is_numeric($max)) {
            $products->where('unit_price', '<=', $max);
        }

        switch ($sort_by) {
            case 'price_low_to_high':
                $products->orderBy('unit_price', 'asc');
                break;

            case 'price_high_to_low':
                $products->orderBy('unit_price', 'desc');
                break;

            case 'new_arrival':
                $products->orderBy('created_at', 'desc');
                break;

            case 'popularity':
                $products->orderBy('num_of_sale', 'desc');
                break;

            case 'top_rated':
                $products->orderBy('rating', 'desc');
                break;

            default:
                $products->orderBy('created_at', 'desc');
                break;
        }

        return new ProductMiniCollection(filter_products($products)->paginate(50));
    }

}
