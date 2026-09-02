<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CustomCategoryController extends Controller
{

    public function index()
    {
        $categories = Category::select(['id', 'name', 'slug', 'parent_id', 'order_level'])
            ->with(['childrenCategories:id,name,slug,parent_id,order_level'])
            ->where('parent_id', 0)
            ->orderBy('order_level', 'asc')
            ->get()
            ->map(function ($data) {
                if (uploaded_asset($data->icon)) {
                    $data->icon = uploaded_asset($data->icon);
                } else {
                    $data->icon = null;
                }
                return $data;
            });
        return response()->json($categories);
    }

    public function topCategoriesList()
    {
        $categories = Category::select(['id', 'name', 'icon', 'slug', 'parent_id', 'order_level'])
            ->with(['childrenCategories:id,name,slug,parent_id,order_level'])
            ->where('top', 1)
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
