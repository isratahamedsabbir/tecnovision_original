<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V2\AizUploadController;
use App\Http\Controllers\Api\V2\SubscriberController;
use App\Http\Controllers\Api\V2\CustomOrderController;
use App\Http\Controllers\Api\V2\Page\HomePageController;
use App\Http\Controllers\Api\V2\CustomCategoryController;
use App\Http\Controllers\Api\V2\CustomCouponController;
use App\Http\Controllers\Api\V2\ForgetPasswordController;
use App\Http\Controllers\Api\V2\ProductQueriesController;
use App\Http\Controllers\Api\V2\Page\CustomPageController;
use App\Http\Controllers\Api\V2\Page\BasicPageLayoutController;


//v2
Route::group(['prefix' => 'v2/', 'middleware' => ['api']], function () {

    Route::get('home-page', [HomePageController::class, 'show']);
    Route::get('basic-page-layout', [BasicPageLayoutController::class, 'show']);

    Route::get('custom-pages', [CustomPageController::class, 'index']);
    Route::get('custom-pages/show/{slug}', [CustomPageController::class, 'show']);


    //File Access
    Route::post('aiz-uploader/get_file_by_ids', [AizUploadController::class, 'get_preview_files']);

    //Subscribers Email
    Route::post('subscriber/store', [SubscriberController::class, 'store']);

    //category
    Route::get('custom-categories', [CustomCategoryController::class, 'index']);
    Route::get('custom-categories/top/list', [CustomCategoryController::class, 'topCategoriesList']);

    //Product
    Route::group(['prefix' => 'custom-products'], function () {
        
        Route::get('{slug}/show',  'App\Http\Controllers\Api\V2\CustomProductController@show')->name('custom-products.show');

        Route::get('{slug}/category/related',  'App\Http\Controllers\Api\V2\CustomProductController@relatedProductsByCategory');
        Route::get('{slug}/brand/related',  'App\Http\Controllers\Api\V2\CustomProductController@relatedProductsByBrand');
        
        Route::get('search',  'App\Http\Controllers\Api\V2\CustomProductController@search');
    });

    Route::post('custom-order/store', [CustomOrderController::class, 'store']);

    // Product Query
    Route::post('product-queries/store', [ProductQueriesController::class, 'store'])->middleware('auth:api');

});

//otp
Route::post('v2/forget-password', [ForgetPasswordController::class, 'forgotPassword']);
Route::post('v2/reset-password', [ForgetPasswordController::class, 'resetPassword']);


Route::group(['prefix' => 'v2/'], function () {
    Route::get('custom-coupon-list', [CustomCouponController::class, 'couponList']);
    Route::get('custom-coupon-check/{code}', [CustomCouponController::class, 'couponCheck']);
    Route::get('custom-coupon-products/{id}', [CustomCouponController::class, 'getCouponProducts']);
});
  
