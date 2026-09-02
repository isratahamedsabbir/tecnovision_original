<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Review;
use App\Models\Attribute;
use App\Models\LastViewedProduct;
use App\Models\Product;
use App\Models\ProductQuery;
use App\Models\ProductStock;
use App\Models\SuggestedProduct;

class ProductDetailCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                $precision = 2;
                $calculable_price = home_discounted_base_price($data, false);
                $calculable_price = number_format($calculable_price, $precision, '.', '');
                $calculable_price = floatval($calculable_price);
                // $calculable_price = round($calculable_price, 2);
                $photo_paths = get_images_path($data->photos);

                $photos = [];


                if (!empty($photo_paths)) {
                    for ($i = 0; $i < count($photo_paths); $i++) {
                        if ($photo_paths[$i] != "") {
                            $item = array();
                            $item['variant'] = "";
                            $item['path'] = $photo_paths[$i];
                            $photos[] = $item;
                        }
                    }
                }

                foreach ($data->stocks as $stockItem) {
                    if ($stockItem->image != null && $stockItem->image != "") {
                        $item = array();
                        $item['variant'] = $stockItem->variant;
                        $item['path'] = uploaded_asset($stockItem->image);
                        $photos[] = $item;
                    }
                }

                $brand = [
                    'id' => 0,
                    'name' => "",
                    'slug' => "",
                    'logo' => "",
                ];

                if ($data->brand != null) {
                    $brand = [
                        'id' => $data->brand->id,
                        'slug' => $data->brand->slug,
                        'name' => $data->brand->getTranslation('name'),
                        'logo' => uploaded_asset($data->brand->logo),
                    ];
                }

                $firstStock = $data->stocks->first();
                $whole_sale = [];
                if (addon_is_activated('wholesale') && $firstStock) {
                    $whole_sale = ProductWholesaleResource::collection($firstStock->wholesalePrices);
                }

                $products_review = $this->productsReview($data->id) ?? [];
                $related_products_by_category = $this->relatedProductsByCategory($data->slug) ?? [];
                $related_products_by_brand = $this->relatedProductsByBrand($data->slug) ?? [];
                $last_viewed_products = $this->lastViewedProducts() ?? [];
                $product_queries = $this->productQueries($data->id) ?? [];
                $product_faqs = $this->productFaqs($data) ?? [];

                $product_stock = $this->productStocks($data) ?? [];

                return [
                    'id' => (int)$data->id,
                    'name' => $data->getTranslation('name'),
                    'slug' => $data->slug,
                    'added_by' => $data->added_by,
                    'seller_id' => $data->user->id,
                    'shop_id' => $data->added_by == 'admin' ? 0 : $data->user->shop->id,
                    'shop_slug' => $data->added_by == 'admin' ? '' : $data->user->shop->slug,
                    'shop_name' => $data->added_by == 'admin' ? translate('In House Product') : $data->user->shop->name,
                    'shop_logo' => $data->added_by == 'admin' ? uploaded_asset(get_setting('header_logo')) : uploaded_asset($data->user->shop->logo) ?? "",
                    'photos' => $photos,
                    'thumbnail_image' => uploaded_asset($data->thumbnail_img),
                    'tags' => explode(',', $data->tags),
                    'price_high_low' => (float)explode('-', home_discounted_base_price($data, false))[0] == (float)explode('-', home_discounted_price($data, false))[1] ? format_price((float)explode('-', home_discounted_price($data, false))[0]) : "From " . format_price((float)explode('-', home_discounted_price($data, false))[0]) . " to " . format_price((float)explode('-', home_discounted_price($data, false))[1]),
                    'choice_options' => $this->convertToChoiceOptions(json_decode($data->choice_options)),
                    'colors' => json_decode($data->colors) ?? [],
                    'has_discount' => home_base_price($data, false) != home_discounted_base_price($data, false),
                    'discount' => "-" . discount_in_percentage($data) . "%",
                    'stroked_price' => home_base_price($data),
                    'main_price' => home_discounted_base_price($data),
                    'calculable_price' => $calculable_price,
                    'currency_symbol' => currency_symbol(),
                    'current_stock' => (int)$data->stocks->sum('qty'),
                    'product_stock'   => $product_stock,
                    'unit' => $data->unit ?? "",
                    'rating' => (float)$data->rating,
                    'rating_count' => (int)Review::where(['product_id' => $data->id])->count(),
                    'earn_point' => (float)$data->earn_point,
                    'description' => $data->getTranslation('description'),
                    'short_description' => $data->getTranslation('short_description'),
                    'downloads' => $data->pdf ? uploaded_asset($data->pdf) : null,
                    'video_link' => $data->video_link != null ?  $data->video_link : "",
                    'brand' => $brand,
                    'link' => route('product', $data->slug),
                    'wholesale' => $whole_sale,
                    'est_shipping_time' => (int)$data->est_shipping_days,
                    'is_wishlisted' => $data->is_wishlisted,
                    //custom fields
                    'model' => $data->model ?? "",
                    'product_type' => $data->type->name ?? "",
                    'made_in' => $data->country->name ?? "",
                    'specification' => $data->specification ?? "",
                    'key_features' => $data->key_features ?? "",
                    'related_products_by_category' => $related_products_by_category,
                    'related_products_by_brand' => $related_products_by_brand,
                    'last_viewed_products' => $last_viewed_products,
                    'products_review' => $products_review,
                    'product_queries' => $product_queries,
                    'faqs' => $product_faqs,
                    'upcoming' => $data->upcoming == 1 ? true : false,
                    //meta
                    'meta_title' => $data->meta_title,
                    'meta_keywords' => $data->meta_keywords,
                    'meta_description' => $data->meta_description,
                    'meta_image' => uploaded_asset($data->meta_img),
                    'suggested_products' => $this->suggestedProducts($data->id),
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

    protected function convertToChoiceOptions($data)
    {
        $result = array();
        $order = 1;
        if ($data) {
            foreach ($data as $key => $choice) {
                $item['order'] = $order++;
                $item['name'] = $choice->attribute_id;
                $item['title'] = Attribute::find($choice->attribute_id)->getTranslation('name');
                $item['options'] = $choice->values;
                array_push($result, $item);
            }
        }
        return $result;
    }

    protected function convertPhotos($data)
    {
        $result = array();
        foreach ($data as $key => $item) {
            array_push($result, uploaded_asset($item));
        }
        return $result;
    }

    public function relatedProductsByCategory($slug)
    {
        $product = Product::where('slug', $slug)->first();

        if ($product) {

            if ($product->unit_price > 0) {
                $price = $product->unit_price;
                $calculable_price = (int) $price * 10 / 100;
                $min_price = $price - $calculable_price;
                $max_price = $price + $calculable_price;
            } else {
                $min_price = 0;
                $max_price = 0;
            }

            return Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('published', '1')
                ->where('upcoming', '0')
                ->whereBetween('unit_price', [$min_price, $max_price])
                ->select('id', 'name', 'slug', 'thumbnail_img', 'unit_price')
                ->take(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'slug' => $item->slug,
                        'thumbnail_img' => uploaded_asset($item->thumbnail_img),
                        'unit_price' => single_price($item->unit_price),
                        //'link' => route('product', $item->slug)
                    ];
                });
        }

        return [];
    }


    public function relatedProductsByBrand($slug)
    {
        $product = Product::where('slug', $slug)->first();
        if ($product) {
            return Product::where('brand_id', $product->brand_id)
                ->where('id', '!=', $product->id)
                ->where('published', '1')
                ->where('upcoming', '0')
                ->select('id', 'name', 'slug', 'thumbnail_img', 'unit_price')
                ->take(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'slug' => $item->slug,
                        'thumbnail_img' => uploaded_asset($item->thumbnail_img),
                        'unit_price' => single_price($item->unit_price),
                        //'link' => route('product', $item->slug)
                    ];
                });
        }

        return [];
    }

    public function lastViewedProducts()
    {
        if (auth('sanctum')->check()) {
            return Product::select('name', 'slug', 'thumbnail_img')
                ->whereIn('id', LastViewedProduct::where('user_id', auth('sanctum')->user()->id)->pluck('product_id'))
                ->get()
                ->map(function ($item) {
                    return [
                        'name' => $item->name,
                        'slug' => $item->slug,
                        'thumbnail_img' => uploaded_asset($item->thumbnail_img),
                    ];
                });
        }
        return [];
    }

    public function productQueries($id)
    {
        return ProductQuery::query()
            ->with(['user' => function ($query) {
                $query->select('id', 'name');
            }])
            ->select('id', 'customer_id', 'question', 'reply', 'created_at')
            ->where('product_id', $id)
            ->where('reply', '!=', null)
            ->where('published', 1)->get();
    }

    public function productFaqs($product)
    {
        return $product->faqs
            ->where('status', 1)
            ->values()
            ->map(function ($faq) {
                return [
                    'id' => $faq->id,
                    'question' => $faq->getTranslation('question'),
                    'answer' => $faq->getTranslation('answer'),
                ];
            });
    }

    public function productsReview($id)
    {
        return Review::select(['id', 'user_id', 'rating', 'comment', 'created_at'])
        ->with(['user' => function ($query) {
            $query->select('id', 'name', 'avatar');
        }])
        ->where('product_id', $id)
        ->where('status', 1)
        ->get()
        ->map(function ($item) {
            if (isset($item->user)) {
                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'user_name' => $item->user->name,
                    'avatar' => $item->user->avatar,
                    'rating' => $item->rating,
                    'comment' => $item->comment,
                    'created_at' => $item->created_at
                ];
            }
            return [];
        });
    }

    public function productStocks($data)
    {
        // Get product stocks
        $productStocks = ProductStock::select(['variant', 'price', 'discount', 'sku', 'qty', 'image'])
            ->where('product_id', $data->id)
            ->get();

        if ($productStocks->isEmpty()) {
            return [];
        }

        return $productStocks->map(function ($item) use ($data) {

            $mainPrice = $item->price;
            $discountedPrice = $mainPrice;

            if ($data->discount > 0) {
                if ($data->discount_type == 'percent') {
                    $discountedPrice = $mainPrice - ($mainPrice * $data->discount / 100);
                } else {
                    $discountedPrice = $mainPrice - $data->discount;
                }
            }

            // Safety: negative price prevent
            $discountedPrice = max($discountedPrice, 0);

            $stroked_price = format_price(convert_price($mainPrice));
            $main_price = format_price(convert_price($item->discount != null && $item->discount >= 0 ? $item->discount : $discountedPrice));
            return [
                'variant'           => $item->variant,
                'stroked_price'     => $stroked_price,
                'main_price'        => $main_price,
                'is_discount'       => $stroked_price > $main_price ? true : false,
                'calculable_price'  => $item->discount != null && $item->discount >= 0 ? $item->discount : $discountedPrice,
                'sku'               => $item->sku,
                'qty'               => $item->qty,
                'image'             => $item->image ? uploaded_asset($item->image) : '',
            ];
        })->toArray();
    }

    public function suggestedProducts($id)
    {
        $suggest = SuggestedProduct::where('product_id', $id)
            ->whereNotNull('suggested_product_id')
            ->pluck('suggested_product_id')
            ->toArray();

        return Product::query()
            ->where('published', '1')
            ->whereIn('id', $suggest)
            ->get()
            ->map(function ($data) {
                return [
                    'id' => $data->id,
                    'slug' => $data->slug,
                    'name' => $data->getTranslation('name'),
                    'thumbnail_image' => uploaded_asset($data->thumbnail_img),
                    'has_discount' => home_base_price($data, false) != home_discounted_base_price($data, false),
                    'discount' => "-" . discount_in_percentage($data) . "%",
                    'stroked_price' => home_base_price($data),
                    'main_price' => home_discounted_base_price($data),
                    'current_stock' => (int)$data->stocks->first()->qty,
                    'rating' => (float) $data->rating,
                    'sales' => (int) $data->num_of_sale,
                    'is_wholesale' => ($data->wholesale_product == 1) ? true : false,
                    'is_new' => $data->is_new,
                    'is_wishlisted' => $data->is_wishlisted,
                    'model' => $data->model,
                    'product_type' => $data->type->name ?? '',
                    'made_in' => $data->country->name ?? '',
                    'upcoming' => $data->upcoming == 1 ? true : false,
                    'links' => [
                        'details' => route('products.show', $data->id),
                    ]
                ];
            });
    }


}
