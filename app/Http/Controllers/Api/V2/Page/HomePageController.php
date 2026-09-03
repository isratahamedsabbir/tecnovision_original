<?php

namespace App\Http\Controllers\Api\V2\Page;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\ProductMiniCustomCollection;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Services\BusinessSettingService;
use Exception;

class HomePageController extends Controller
{

    protected $businessSettingService;

    public function __construct(BusinessSettingService $businessSettingService)
    {
        $this->businessSettingService = $businessSettingService;
    }

    public function show()
    {
        try {
            $typeKeys = [
                'home_slider_images',
                'home_slider_links',
                'top_banner_image',
                'top_banner_link',
                'topbar_video_link',
                'bottom_banner_image',
                'bottom_banner_link'
            ];
            $singleImage = ['top_banner_image', 'bottom_banner_image'];
            $multipleImage = ['home_slider_images'];
            $filteredCms = $this->businessSettingService->dataLoad($typeKeys, $singleImage, $multipleImage);

            $homePage = Page::where('type', 'home_page')->first();
            if ($homePage) {
                $filteredCms['seo'] = [
                    'meta_title' => $homePage->meta_title,
                    'meta_description' => $homePage->meta_description,
                    'meta_keywords' => $homePage->keywords,
                    'meta_image' => $homePage->meta_image ? uploaded_asset($homePage->meta_image) : null,
                ];
            }

            $upcoming = Product::where('upcoming', 1)->where('published', 1)->physical();
            $filteredCms['upcoming'] = new ProductMiniCustomCollection(filter_products($upcoming)->latest()->get());

            $bestSellers = Product::where('best_seller', 1)->where('published', 1)->physical();
            $filteredCms['best_seller'] = new ProductMiniCustomCollection(filter_products($bestSellers)->latest()->get());

            $homeCategoryIds = json_decode(get_setting('home_categories'));
            $categoryWiseProducts = [];

            if (!empty($homeCategoryIds)) {
                $categories = Category::with('coverImage')
                    ->whereIn('id', $homeCategoryIds)
                    ->get();

                foreach ($categories as $category) {
                    $products = Product::where('category_id', $category->id)->where('published', 1);

                    $categoryWiseProducts[] = [
                        'id' => $category->id,
                        'name' => $category->getTranslation('name'),
                        'slug' => $category->slug,
                        'cover_image' => uploaded_asset($category->cover_image),
                        'products' => new ProductMiniCustomCollection(
                            filter_products($products)->latest()->take(5)->get()
                        ),
                    ];
                }
            }

            $filteredCms['category_wise_product'] = $categoryWiseProducts;

            $data = [
                'status' => true,
                'message' => 'fetched successfully',
                'data' => $filteredCms
            ];
        } catch (Exception $e) {
            $data = [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
        return response()->json($data, 200);
    }
}
