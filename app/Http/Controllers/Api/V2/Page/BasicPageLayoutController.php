<?php

namespace App\Http\Controllers\Api\V2\Page;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\BusinessSettingService;
use Exception;

class BasicPageLayoutController extends Controller
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
                'site_icon',
                'header_logo',
                'topbar_banner_link',
                'helpline_number',
                'helpline_email',
                'footer_title',
                'footer_address',
                'footer_logo',
                'copyright',
                'contact_email',
                'contact_phone',
                'contact_whatsapp',
                'facebook_link',
                'twitter_link',
                'instagram_link',
                'youtube_link'
            ];
            $singleImage = ['site_icon', 'header_logo', 'footer_logo'];
            $multipleImage = [];
            $filteredCms = $this->businessSettingService->dataLoad($typeKeys, $singleImage, $multipleImage);
            
            $pages = Page::where('status', 1)->get(['id', 'title', 'slug']);
            $pages->map(function ($page) {
                $page->url = config('app.frontend') .'/'. $page->slug;
                return $page;
            });

            $data = [
                'status' => true,
                'message' => 'fetched successfully',
                'data' => $filteredCms,
                'pages' => $pages
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
