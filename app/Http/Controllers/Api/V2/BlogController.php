<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\BlogCollection;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function blog_list(Request $request, $category_slug = null)
    {
        $selected_categories = [];
        $search              = null;
        $blogs               = Blog::query();

        if ($request->has('search')) {
            $search = $request->search;
            $blogs->where(function ($q) use ($search) {
                foreach (explode(' ', trim($search)) as $word) {
                    $q->where('title', 'like', '%' . $word . '%')
                        ->orWhere('short_description', 'like', '%' . $word . '%');
                }
            });

            $case1 = $search . '%';
            $case2 = '%' . $search . '%';

            $blogs->orderByRaw("CASE
                WHEN title LIKE '$case1' THEN 1
                WHEN title LIKE '$case2' THEN 2
                ELSE 3
                END");
        }

        if ($request->has('selected_categories')) {
            $selected_categories = $request->selected_categories;
            $blog_categories     = BlogCategory::whereIn('slug', $selected_categories)->pluck('id')->toArray();

            $blogs->whereIn('category_id', $blog_categories);
        }

        $blogs = $blogs->where('status', 1);

        if ($category_slug) {
            $blog_category = BlogCategory::where('slug', $category_slug)->first();
            if(!$blog_category) return response()->json(['result' => false, 'message' => 'Blog category not found'], 404);
            $blogs->where('category_id', $blog_category->id);
        }

        $blogs = $blogs->orderBy('created_at', 'desc')->paginate(12);

        $recent_blogs = Blog::select('id', 'title', 'slug', 'banner', 'created_at')
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->limit(9)
            ->get()
            ->map(function ($data) {
                return [
                    'id'         => $data->id,
                    'title'      => $data->title,
                    'slug'       => $data->slug,
                    'banner'     => uploaded_asset($data->banner),
                    'created_at' => date('j F Y, g:i a', strtotime($data->created_at)),
                ];
            });

        return response()->json([
            'result'              => true,
            'blogs'               => new BlogCollection($blogs),
            'selected_categories' => $selected_categories,
            'search'              => $search,
            'recent_blogs'        => $recent_blogs,
        ]);
    }

    public function blog_details($slug)
    {
        $blog = Blog::where('slug', $slug)
            ->with('category:id,category_name')
            ->first();

        if (!$blog) {
            return response()->json([
                'result' => false,
                'message' => 'Blog not found',
            ], 404);
        }

        $blog = [
            'id'                => $blog->id,
            'title'             => $blog->title,
            'slug'              => $blog->slug,
            'short_description' => $blog->short_description,
            'description'       => $blog->description,
            'banner'            => uploaded_asset($blog->banner),
            'meta_img'          => uploaded_asset($blog->meta_img),
            'meta_title'        => $blog->meta_title,
            'meta_description'  => $blog->meta_description,
            'status'            => $blog->status,
            'category'          => optional($blog->category)->category_name,
            'category_id'       => $blog->category_id,
            'created_at'        => date('j F Y, g:i a', strtotime($blog->created_at)),
            'updated_at'        => date('j F Y, g:i a', strtotime($blog->updated_at)),
        ];

        $recent_blogs = Blog::select('id', 'title', 'slug', 'banner', 'created_at')
            ->where('id', '!=', $blog['id'])
            ->where('status', 1)
            ->latest()
            ->limit(9)
            ->get()
            ->map(function ($data) {
                return [
                    'id'         => $data->id,
                    'title'      => $data->title,
                    'slug'       => $data->slug,
                    'banner'     => uploaded_asset($data->banner),
                    'created_at' => date('j F Y, g:i a', strtotime($data->created_at)),
                ];
            });

        $related_blogs = Blog::select('id', 'title', 'slug', 'banner', 'created_at')
            ->where('category_id', $blog['category_id'])
            ->where('id', '!=', $blog['id'])
            ->where('status', 1)
            ->latest()
            ->limit(9)
            ->get()
            ->map(function ($data) {
                return [
                    'id'         => $data->id,
                    'title'      => $data->title,
                    'slug'       => $data->slug,
                    'banner'     => uploaded_asset($data->banner),
                    'created_at' => date('j F Y, g:i a', strtotime($data->created_at)),
                ];
            });

        return response()->json([
            'result'        => true,
            'blog'          => $blog,
            'recent_blogs'  => $recent_blogs,
            'related_blogs' => $related_blogs,
        ]);
    }


    public function test()
    {
        return response()->json([
            'result'  => true,
            'message' => 'okk...',
        ]);
    }

    public function blog_category()
    {
        $blog_categories = BlogCategory::select(['id', 'category_name', 'slug'])->get()->map(function ($data) {
            return [
                'id'   => $data->id,
                'name' => $data->category_name,
                'slug' => $data->slug,
            ];
        });
        return response()->json([
            'code'            => 200,
            'result'          => true,
            'blog_categories' => $blog_categories,
        ]);
    }
}
