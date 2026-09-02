<?php

namespace App\Models;

use App;
use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;
use Carbon\Carbon;

class Product extends Model
{
    use PreventDemoModeChanges;

    protected $guarded = ['choice_attributes'];

    protected $with = ['product_translations', 'taxes', 'thumbnail'];


    protected $appends = ['is_new', 'is_wishlisted'];

    public function getIsNewAttribute()
    {
        return (Carbon::now()->diffInDays($this->created_at) <= 30);
    }

    public function getIsWishlistedAttribute()
    {
        if (auth("sanctum")->check()) {
            return Wishlist::where('user_id', auth("sanctum")->user()->id)->where('product_id', $this->id)->exists();
        }
        return false;
    }

    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang == false ? App::getLocale() : $lang;
        $product_translations = $this->product_translations->where('lang', $lang)->first();
        return $product_translations != null ? $product_translations->$field : $this->$field;
    }

    public function product_translations()
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function main_category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function frequently_bought_products()
    {
        return $this->hasMany(FrequentlyBoughtProduct::class);
    }

    public function product_categories()
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function product_queries()
    {
        return $this->hasMany(ProductQuery::class);
    }

    public function product_questions()
    {
        return $this->hasMany(ProductQuestion::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function taxes()
    {
        return $this->hasMany(ProductTax::class);
    }

    public function flash_deal_products()
    {
        return $this->hasMany(FlashDealProduct::class);
    }

    public function bids()
    {
        return $this->hasMany(AuctionProductBid::class);
    }

    public function thumbnail()
    {
        return $this->belongsTo(Upload::class, 'thumbnail_img');
    }

    public function scopePhysical($query)
    {
        return $query->where('digital', 0);
    }

    public function scopeDigital($query)
    {
        return $query->where('digital', 1);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function scopeIsApprovedPublished($query)
    {
        return $query->where('approved', '1')->where('published', 1);
    }

    public function last_viewed_products()
    {
        return $this->hasMany(LastViewedProduct::class);
    }

    public function warranty()
    {
        return $this->belongsTo(Warranty::class);
    }

    public function warrantyNote()
    {
        return $this->belongsTo(Note::class, 'warranty_note_id');
    }

    public function refundNote()
    {
        return $this->belongsTo(Note::class, 'refund_note_id');
    }

    public function filters()
    {
        return $this->belongsToMany(Filter::class, 'filters_products', 'product_id', 'filter_id');
    }

    public function type(){
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    public function country(){
        return $this->belongsTo(Country::class);
    }

    public function suggestedProducts()
    {
        return $this->hasMany(SuggestedProduct::class, 'product_id');
    }

    public function suggestions()
    {
        return $this->belongsToMany(Product::class, 'suggested_products', 'product_id', 'suggested_product_id');
    }

    public function faqs()
    {
        return $this->belongsToMany(Faq::class)->withTimestamps();
    }
    
}
