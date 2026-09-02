<?php

namespace App\Http\Controllers\Api\V2\Page;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Support\Facades\Redis;

class CustomPageController extends Controller
{
    public function index()
    {
        $pages = Redis::get('pages');
        if (!$pages) {
            $pages = Page::select('type', 'title', 'slug')
            ->where('type', '!=', 'home_page')
            ->where('status', 'active')
            ->get();
            Redis::set('pages', json_encode($pages));
        } else {
            $pages = json_decode($pages);
        }

        return response()->json($pages);
    }

    public function show($slug)
    {
        $page = Page::select('title', 'slug', 'content', 'meta_title', 'meta_description', 'keywords', 'meta_image')
        ->where('slug', $slug)
        ->first();
        return response()->json($page);
    }

}
