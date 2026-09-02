<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\CategoryCollection;
use App\Models\Category;

class SubCategoryController extends Controller
{
    /* public function index($id)
    {
        return new CategoryCollection(Category::where('parent_id', $id)->get());
    }
 */
    public function index($slug)
    {
        $category = Category::where('slug', $slug)->first();
        $categories = Category::select(['id', 'name', 'icon', 'slug', 'parent_id', 'order_level'])
            ->where('parent_id', $category->id)
            ->orderBy('order_level', 'asc')
            ->get()
            ->map(function ($data) {
                if (uploaded_asset($data->icon)) {
                    $data->icon = uploaded_asset($data->icon);
                } else {
                    $data->icon = null;
                }

                return [
                    'id' => $data->id,
                    'name' => $data->name,
                    'icon' => $data->icon,
                    'slug' => $data->slug
                ];
            });
        return response()->json($categories);
    }
}
