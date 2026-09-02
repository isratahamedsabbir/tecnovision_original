<?php

namespace App\Services;

use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Redis;


class BusinessSettingService
{

    public function dataCheckInRedis()
    {
        $businessSetting = Redis::get('businessSetting');

        if (!$businessSetting) {
            $businessSetting = BusinessSetting::select('type', 'value')->get();
            Redis::set('businessSetting', json_encode($businessSetting));
        } else {
            $businessSetting = json_decode($businessSetting, true);
        }

        return $businessSetting;
    }


    public function multipleFile($files)
    {
        $images = json_decode($files, true);
        $result = [];

        if (is_array($images)) {
            foreach ($images as $key => $value) {
                $result[$key] = uploaded_asset($value);
            }
        }

        return $result;
    }

    function imageLinkMarge($image, $link)
    {
        $result = [];
        foreach ($image as $key => $value) {
            
            $result[$key]['image'] = $value;
            $result[$key]['link'] = $link[$key] ?? '';
        }
        return $result;
    }
    

    public function dataLoad($typeKeys, $singleImage, $multipleImage) {
        $businessSetting = $this->dataCheckInRedis();
        $filteredCms = array_fill_keys($typeKeys, null);

        foreach ($businessSetting as $value) {
            if (in_array($value['type'], $typeKeys)) {

                if (in_array($value['type'], $singleImage)) {
                    $filteredCms[$value['type']] = uploaded_asset($value['value']);
                } elseif (in_array($value['type'], $multipleImage)) {
                    $filteredCms[$value['type']] = $this->multipleFile($value['value']);
                } else {
                    $filteredCms[$value['type']] = $value['value'];
                }
                
            }
        }

        if (isset($filteredCms['home_slider_links']) && isset($filteredCms['home_slider_images'])) {
            $filteredCms['home_slider'] = $this->imageLinkMarge(
                $filteredCms['home_slider_images'],
                json_decode($filteredCms['home_slider_links'])
            );
            unset($filteredCms['home_slider_images'], $filteredCms['home_slider_links']);
        }

        return array_filter($filteredCms, function($value) {
            return $value !== null;
        });
    }

}
