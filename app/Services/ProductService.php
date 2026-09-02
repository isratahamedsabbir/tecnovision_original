<?php

namespace App\Services;

use AizPackages\CombinationGenerate\Services\CombinationService;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Color;
use App\Models\Filter;
use App\Models\Product;
use App\Models\SellerCategory;
use App\Models\Shop;
use App\Models\User;
use App\Models\Wishlist;
use App\Utility\ProductUtility;
use Combinations;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductService
{
    public function store(array $data)
    {
        $collection = collect($data);

        $approved = 1;
        if (auth()->user()->user_type == 'seller') {
            $user_id = auth()->user()->id;
            if (get_setting('product_approve_by_admin') == 1) {
                $approved = 0;
            }
        } else {
            $user_id = User::where('user_type', 'admin')->first()->id;
        }
        $tags = array();
        if ($collection['tags'][0] != null) {
            foreach (json_decode($collection['tags'][0]) as $key => $tag) {
                array_push($tags, $tag->value);
            }
        }
        $collection['tags'] = implode(',', $tags);
        $discount_start_date = null;
        $discount_end_date   = null;
        if ($collection['date_range'] != null) {
            $date_var               = explode(" to ", $collection['date_range']);
            $discount_start_date = strtotime($date_var[0]);
            $discount_end_date   = strtotime($date_var[1]);
        }
        unset($collection['date_range']);

        if ($collection['meta_title'] == null) {
            $collection['meta_title'] = $collection['name'];
        }

        if ($collection['meta_description'] == null) {
            $collection['meta_description'] = strip_tags($collection['description']);
        }

        if ($collection['meta_img'] == null) {
            $collection['meta_img'] = $collection['thumbnail_img'];
        }

        if ($collection['meta_keywords'] == null) {
            $meta_keywords = preg_replace('/\s+/', ',', strip_tags($collection['description']));
            $collection['meta_keywords'] = $meta_keywords;
        }

        $shipping_cost = 0;
        if (isset($collection['shipping_type'])) {
            if ($collection['shipping_type'] == 'free') {
                $shipping_cost = 0;
            } elseif ($collection['shipping_type'] == 'flat_rate') {
                $shipping_cost = $collection['flat_shipping_cost'];
            }
        }
        unset($collection['flat_shipping_cost']);

        $slug = Str::slug($collection['name']);
        $same_slug_count = Product::where('slug', 'LIKE', $slug . '%')->count();
        $slug_suffix = $same_slug_count ? '-' . $same_slug_count + 1 : '';
        $slug .= $slug_suffix;

        $colors = json_encode(array());
        if (
            isset($collection['colors_active']) &&
            $collection['colors_active'] &&
            $collection['colors'] &&
            count($collection['colors']) > 0
        ) {
            $colors = json_encode($collection['colors']);
        }

        $options = ProductUtility::get_attribute_options($collection);

        $combinations = (new CombinationService())->generate_combination($options);

        if (count($combinations) > 0) {
            foreach ($combinations as $key => $combination) {
                $str = ProductUtility::get_combination_string($combination, $collection);

                unset($collection['price_' . str_replace('.', '_', $str)]);
                unset($collection['sku_' . str_replace('.', '_', $str)]);
                unset($collection['qty_' . str_replace('.', '_', $str)]);
                unset($collection['img_' . str_replace('.', '_', $str)]);
            }
        }

        unset($collection['colors_active']);

        $choice_options = array();
        if (isset($collection['choice_no']) && $collection['choice_no']) {
            $str = '';
            $item = array();
            foreach ($collection['choice_no'] as $key => $no) {
                $str = 'choice_options_' . $no;
                $item['attribute_id'] = $no;
                $attribute_data = array();
                // foreach (json_decode($request[$str][0]) as $key => $eachValue) {

                if(isset($collection[$str]) && count($collection[$str]) > 0){
                    foreach ($collection[$str] as $key => $eachValue) {
                        // array_push($data, $eachValue->value);
                        array_push($attribute_data, $eachValue);
                    }
                } 
                unset($collection[$str]);

                $item['values'] = $attribute_data;
                array_push($choice_options, $item);
            }
        }

        $choice_options = json_encode($choice_options, JSON_UNESCAPED_UNICODE);

        if (isset($collection['choice_no']) && $collection['choice_no']) {
            $attributes = json_encode($collection['choice_no']);
            unset($collection['choice_no']);
        } else {
            $attributes = json_encode(array());
        }

        $published = 1;
        if ($collection['button'] == 'unpublish' || $collection['button'] == 'draft') {
            $published = 0;
        }
        unset($collection['button']);

        $collection['has_warranty'] = isset($collection['has_warranty']) ? 1 : 0;

        if (isset($collection['filters']) && is_array($collection['filters'])) {
            $filter_ids = [];
            foreach ($collection['filters'] as $filter) {
                $existing_filter = Filter::where('name', $filter)->first();
                if ($existing_filter) {
                    $filter_ids[] = $existing_filter->id;
                } else {
                    $new_filter = Filter::create(['name' => $filter]);
                    $filter_ids[] = $new_filter->id;
                }
            }
            $collection['filters'] = json_encode($filter_ids);
        }

        $data = $collection->merge(compact(
            'user_id',
            'approved',
            'discount_start_date',
            'discount_end_date',
            'shipping_cost',
            'slug',
            'colors',
            'choice_options',
            'attributes',
            'published',
        ))->toArray();

        return Product::create($data);
    }

    public function update(array $data, Product $product)
    {
        $collection = collect($data);

        $slug = Str::slug($collection['name']);
        $slug = $collection['slug'] ? Str::slug($collection['slug']) : Str::slug($collection['name']);
        $same_slug_count = Product::where('slug', 'LIKE', $slug . '%')->count();
        $slug_suffix = $same_slug_count > 1 ? '-' . $same_slug_count + 1 : '';
        $slug .= $slug_suffix;

        if (addon_is_activated('refund_request') && !isset($collection['refundable'])) {
            $collection['refundable'] = 0;
        }

        if (!isset($collection['is_quantity_multiplied'])) {
            $collection['is_quantity_multiplied'] = 0;
        }

        if (!isset($collection['cash_on_delivery'])) {
            $collection['cash_on_delivery'] = 0;
        }
        if (!isset($collection['featured'])) {
            $collection['featured'] = 0;
        }
        if (!isset($collection['upcoming'])) {
            $collection['upcoming'] = 0;
        }
        if (!isset($collection['best_seller'])) {
            $collection['best_seller'] = 0;
        }
        if (!isset($collection['todays_deal'])) {
            $collection['todays_deal'] = 0;
        }


        $tags = array();
        if ($collection['tags'][0] != null) {
            foreach (json_decode($collection['tags'][0]) as $key => $tag) {
                array_push($tags, $tag->value);
            }
        }
        $collection['tags'] = implode(',', $tags);
        $discount_start_date = null;
        $discount_end_date   = null;
        if ($collection['date_range'] != null) {
            $date_var               = explode(" to ", $collection['date_range']);
            $discount_start_date = strtotime($date_var[0]);
            $discount_end_date   = strtotime($date_var[1]);
        }
        unset($collection['date_range']);

        if ($collection['meta_title'] == null) {
            $collection['meta_title'] = $collection['name'];
        }

        if ($collection['meta_description'] == null) {
            $collection['meta_description'] = strip_tags($collection['description']);
        }

        if ($collection['meta_img'] == null) {
            $collection['meta_img'] = $collection['thumbnail_img'];
        }

        if ($collection['meta_keywords'] == null) {
            $meta_keywords = preg_replace('/\s+/', ',', strip_tags($collection['description']));
            $collection['meta_keywords'] = $meta_keywords;
        }

        if ($collection['lang'] != env("DEFAULT_LANGUAGE")) {
            unset($collection['name']);
            unset($collection['unit']);
            unset($collection['description']);
        }
        unset($collection['lang']);


        $shipping_cost = 0;
        if (isset($collection['shipping_type'])) {
            if ($collection['shipping_type'] == 'free') {
                $shipping_cost = 0;
            } elseif ($collection['shipping_type'] == 'flat_rate') {
                $shipping_cost = $collection['flat_shipping_cost'];
            }
        }
        unset($collection['flat_shipping_cost']);

        $colors = json_encode(array());
        if (
            isset($collection['colors_active']) &&
            $collection['colors_active'] &&
            $collection['colors'] &&
            count($collection['colors']) > 0
        ) {
            $colors = json_encode($collection['colors']);
        }

        $options = ProductUtility::get_attribute_options($collection);

        $combinations = (new CombinationService())->generate_combination($options);
        if (count($combinations) > 0) {
            foreach ($combinations as $key => $combination) {
                $str = ProductUtility::get_combination_string($combination, $collection);

                unset($collection['price_' . str_replace('.', '_', $str)]);
                unset($collection['sku_' . str_replace('.', '_', $str)]);
                unset($collection['qty_' . str_replace('.', '_', $str)]);
                unset($collection['img_' . str_replace('.', '_', $str)]);
            }
        }

        unset($collection['colors_active']);

        $choice_options = array();
        if (isset($collection['choice_no']) && $collection['choice_no']) {
            $str = '';
            $item = array();
            foreach ($collection['choice_no'] as $key => $no) {
                $str = 'choice_options_' . $no;
                $item['attribute_id'] = $no;
                $attribute_data = array();

                if(isset($collection[$str])){
                    // foreach (json_decode($request[$str][0]) as $key => $eachValue) {
                    foreach ($collection[$str] as $key => $eachValue) {
                        // array_push($data, $eachValue->value);
                        array_push($attribute_data, $eachValue);
                    }
                }
                unset($collection[$str]);

                $item['values'] = $attribute_data;
                array_push($choice_options, $item);
            }
        }

        $choice_options = json_encode($choice_options, JSON_UNESCAPED_UNICODE);

        if (isset($collection['choice_no']) && $collection['choice_no']) {
            $attributes = json_encode($collection['choice_no']);
            unset($collection['choice_no']);
        } else {
            $attributes = json_encode(array());
        }

        $collection['has_warranty'] = isset($collection['has_warranty']) ? 1 : 0;

        if (isset($collection['filters']) && is_array($collection['filters'])) {
            $filter_ids = [];
            foreach ($collection['filters'] as $filter) {
                $existing_filter = Filter::where('name', $filter)->first();
                if ($existing_filter) {
                    $filter_ids[] = $existing_filter->id;
                } else {
                    $new_filter = Filter::create(['name' => $filter]);
                    $filter_ids[] = $new_filter->id;
                }
            }
            $collection['filters'] = json_encode($filter_ids);
        }

        unset($collection['button']);

        $data = $collection->merge(compact(
            'discount_start_date',
            'discount_end_date',
            'shipping_cost',
            'slug',
            'colors',
            'choice_options',
            'attributes',
        ))->toArray();

        $product->update($data);

        return $product;
    }

    public function product_duplicate_store($product)
    {
        $product_new = $product->replicate();
        $product_new->slug = $product_new->slug . '-' . Str::random(5);
        $product_new->approved = (get_setting('product_approve_by_admin') == 1 && $product->added_by != 'admin') ? 0 : 1;
        $product_new->save();

        return $product_new;
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->product_translations()->delete();
        $product->categories()->detach();
        $product->stocks()->delete();
        $product->taxes()->delete();
        $product->wishlists()->delete();
        $product->carts()->delete();
        $product->frequently_bought_products()->delete();
        $product->last_viewed_products()->delete();
        $product->flash_deal_products()->delete();
        deleteProductReview($product);
        Product::destroy($id);
    }

    public function product_search(array $data)
    {
        $collection     = collect($data);
        $auth_user      = auth()->user();
        $productType    = $collection['product_type'];
        $products       = Product::query();

        if ($collection['category'] != null) {
            $category = Category::with('childrenCategories')->find($collection['category']);
            $products = $category->products();
        }

        $products = in_array($auth_user->user_type, ['admin', 'staff']) ? $products->where('products.added_by', 'admin') : $products->where('products.user_id', $auth_user->id);
        $products->where('published', '1')->where('auction_product', 0)->where('approved', '1');

        if ($productType == 'physical') {
            $products->where('digital', 0)->where('wholesale_product', 0);
        } elseif ($productType == 'digital') {
            $products->where('digital', 1);
        } elseif ($productType == 'wholesale') {
            $products->where('wholesale_product', 1);
        }

        if ($collection['product_id'] != null) {
            $products->where('id', '!=', $collection['product_id']);
        }

        if ($collection['search_key'] != null) {
            $products->where('name', 'like', '%' . $collection['search_key'] . '%');
        }

        return $products->limit(20)->get();
    }

    public function setCategoryWiseDiscount(array $data)
    {
        try {
            $auth_user      = auth()->user();
            $discount_start_date = null;
            $discount_end_date   = null;
            $seller_discount_start_date = null;
            $seller_discount_end_date = null;
            $admin_discount_start_date = null;
            $admin_discount_end_date = null;

            if ($data['date_range'] != null) {
                $date_var               = explode(" to ", $data['date_range']);
                $discount_start_date = strtotime($date_var[0]);
                $discount_end_date   = strtotime($date_var[1]);
                $seller_discount_start_date = $discount_start_date;
                $seller_discount_end_date = $discount_end_date;
                $admin_discount_start_date = $discount_start_date;
                $admin_discount_end_date = $discount_end_date;
            }
            $seller_product_discount =  isset($data['seller_product_discount']) ? $data['seller_product_discount'] : null;
            $admin_id = User::where('user_type', 'admin')->first()->id;

            $admin_discount = null;
            $seller_discount = null;

            $category = Category::find($data['category_id']);
            $products = Product::where('category_id', $data['category_id'])->where('auction_product', 0);

            if (in_array($auth_user->user_type, ['admin', 'staff'])) {
                $admin_discount = $data['discount'];
                if ($seller_product_discount == 1) {
                    $shops = Shop::all();
                    foreach ($shops as $shop) {
                        $seller_cat = SellerCategory::where('category_id', $data['category_id'])
                            ->where('seller_id', $shop->user_id)
                            ->first();

                        if ($seller_cat) {
                            // Update if record exists
                            $seller_cat->update([
                                'discount' => $admin_discount,
                                'discount_start_date' => $admin_discount_start_date,
                                'discount_end_date' => $admin_discount_end_date,
                            ]);
                        } else {
                            // Create if not found
                            SellerCategory::create([
                                'category_id' => $data['category_id'],
                                'seller_id' => $shop->user_id,
                                'discount' => $admin_discount,
                                'discount_start_date' => $admin_discount_start_date,
                                'discount_end_date' => $admin_discount_end_date,
                            ]);
                        }
                    }
                }

                if ($seller_product_discount == 0) {
                    $products->where('user_id', $admin_id);
                }
                // Save to category
                $category->update([
                    'discount' => $admin_discount,
                    'discount_start_date' => $admin_discount_start_date,
                    'discount_end_date' => $admin_discount_end_date,
                ]);
            } elseif ($auth_user->user_type == 'seller') {
                $products->where('user_id', $auth_user->id);
                $seller_discount = $data['discount'];
                //save to sellerCategory
                $sellerCat = SellerCategory::where('seller_id', $auth_user->id)
                    ->where('category_id', $data['category_id'])
                    ->first();

                if ($sellerCat) {
                    $sellerCat->discount = $seller_discount;
                    $sellerCat->discount_start_date = $seller_discount_start_date;
                    $sellerCat->discount_end_date = $seller_discount_end_date;
                    $sellerCat->save();
                } else {
                    $sellerCat = new SellerCategory();
                    $sellerCat->seller_id = $auth_user->id;
                    $sellerCat->category_id = $data['category_id'];
                    $sellerCat->discount = $seller_discount;
                    $sellerCat->discount_start_date = $seller_discount_start_date;
                    $sellerCat->discount_end_date = $seller_discount_end_date;
                    $sellerCat->save();
                }
            }

            $products->update([
                'discount' => $data['discount'],
                'discount_type' => 'percent',
                'discount_start_date' => $discount_start_date,
                'discount_end_date' => $discount_end_date,
            ]);
            return 1;
        } catch (\Exception $e) {
            Log::error('Discount update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'input_data' => $data,
            ]);

            return 0;
        }
    }
}
