<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use App\Models\Country;
use App\Models\PaymentMethod;
use App\Models\Zone;
use Artisan;
use CoreComponentRepository;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Str;
use DB;
use ZipArchive;
use Illuminate\Support\Facades\Redis;

class BusinessSettingsController extends Controller
{
    public function __construct()
    {
        // Staff Permission Check
        $this->middleware(['permission:seller_commission_configuration'])->only('vendor_commission');
        $this->middleware(['permission:seller_verification_form_configuration'])->only('seller_verification_form');
        $this->middleware(['permission:general_settings'])->only('general_setting');
        $this->middleware(['permission:features_activation'])->only('activation');
        $this->middleware(['permission:smtp_settings'])->only('smtp_settings');
        $this->middleware(['permission:payment_methods_configurations'])->only('payment_method');
        $this->middleware(['permission:order_configuration'])->only('order_configuration');
        $this->middleware(['permission:file_system_&_cache_configuration'])->only('file_system');
        $this->middleware(['permission:social_media_logins'])->only('social_login');
        $this->middleware(['permission:whatsapp_chat'])->only('whatsappChat');
        $this->middleware(['permission:facebook_comment'])->only('facebook_comment');
        $this->middleware(['permission:analytics_tools_configuration'])->only('google_analytics');
        $this->middleware(['permission:google_recaptcha_configuration'])->only('google_recaptcha');
        $this->middleware(['permission:google_map_setting'])->only('google_map');
        $this->middleware(['permission:google_firebase_setting'])->only('google_firebase');
        $this->middleware(['permission:shipping_configuration'])->only('shipping_configuration');
    }

    public function general_setting(Request $request)
    {
        CoreComponentRepository::instantiateShopRepository();
        CoreComponentRepository::initializeCache();
        return view('backend.setup_configurations.general_settings');
    }

    public function activation(Request $request)
    {
        CoreComponentRepository::instantiateShopRepository();
        CoreComponentRepository::initializeCache();
        return view('backend.setup_configurations.activation');
    }

    public function social_login(Request $request)
    {
        CoreComponentRepository::instantiateShopRepository();
        CoreComponentRepository::initializeCache();
        return view('backend.setup_configurations.social_login');
    }

    public function smtp_settings(Request $request)
    {
        CoreComponentRepository::instantiateShopRepository();
        CoreComponentRepository::initializeCache();
        return view('backend.setup_configurations.smtp_settings');
    }

    public function google_analytics(Request $request)
    {
        CoreComponentRepository::instantiateShopRepository();
        CoreComponentRepository::initializeCache();
        return view('backend.setup_configurations.google_configuration.google_analytics');
    }

    public function google_recaptcha(Request $request)
    {
        CoreComponentRepository::instantiateShopRepository();
        CoreComponentRepository::initializeCache();
        return view('backend.setup_configurations.google_configuration.google_recaptcha');
    }

    public function google_map(Request $request)
    {
        CoreComponentRepository::instantiateShopRepository();
        CoreComponentRepository::initializeCache();
        return view('backend.setup_configurations.google_configuration.google_map');
    }

    public function google_firebase(Request $request)
    {
        CoreComponentRepository::instantiateShopRepository();
        CoreComponentRepository::initializeCache();
        return view('backend.setup_configurations.google_configuration.google_firebase');
    }

    public function whatsappChat(Request $request)
    {
        CoreComponentRepository::instantiateShopRepository();
        CoreComponentRepository::initializeCache();
        return view('backend.setup_configurations.whatsapp_chat');
    }

    public function facebook_comment(Request $request)
    {
        CoreComponentRepository::instantiateShopRepository();
        CoreComponentRepository::initializeCache();
        return view('backend.setup_configurations.facebook_configuration.facebook_comment');
    }

    public function payment_method(Request $request)
    {
        CoreComponentRepository::instantiateShopRepository();
        CoreComponentRepository::initializeCache();
        $payment_methods = PaymentMethod::whereNull('addon_identifier')->get();
        return view('backend.setup_configurations.payment_method.index', compact('payment_methods'));
    }

    public function file_system(Request $request)
    {
        CoreComponentRepository::instantiateShopRepository();
        CoreComponentRepository::initialize();
        return view('backend.setup_configurations.file_system');
    }

    /**
     * Update the API key's for payment methods.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function payment_method_update(Request $request)
    {
        foreach ($request->types as $key => $type) {
            $this->overWriteEnvFile($type, $request[$type]);
        }

        $business_settings = BusinessSetting::where('type', $request->payment_method . '_sandbox')->first();
        if ($business_settings != null) {
            if ($request->has($request->payment_method . '_sandbox')) {
                $business_settings->value = 1;
                $business_settings->save();
            } else {
                $business_settings->value = 0;
                $business_settings->save();
            }
        }

        Artisan::call('cache:clear');

        flash(translate("Settings updated successfully"))->success();
        return back();
    }

    /**
     * Update the API key's for GOOGLE analytics.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function google_analytics_update(Request $request)
    {
        foreach ($request->types as $key => $type) {
            $this->overWriteEnvFile($type, $request[$type]);
        }

        $business_settings = BusinessSetting::where('type', 'google_analytics')->first();

        if ($request->has('google_analytics')) {
            $business_settings->value = 1;
            $business_settings->save();
        } else {
            $business_settings->value = 0;
            $business_settings->save();
        }

        Artisan::call('cache:clear');

        flash(translate("Settings updated successfully"))->success();
        return back();
    }

    public function google_recaptcha_update(Request $request)
    {
        foreach ($request->types as $key => $type) {
            $this->overWriteEnvFile($type, $request[$type]);
        }

        $business_settings = BusinessSetting::where('type', 'google_recaptcha')->first();

        if ($request->has('google_recaptcha')) {
            $business_settings->value = 1;
            $business_settings->save();
        } else {
            $business_settings->value = 0;
            $business_settings->save();
        }

        Artisan::call('cache:clear');

        flash(translate("Settings updated successfully"))->success();
        return back();
    }

    public function google_map_update(Request $request)
    {
        foreach ($request->types as $key => $type) {
            $this->overWriteEnvFile($type, $request[$type]);
        }

        $business_settings = BusinessSetting::where('type', 'google_map')->first();

        if ($request->has('google_map')) {
            $business_settings->value = 1;
            $business_settings->save();
        } else {
            $business_settings->value = 0;
            $business_settings->save();
        }

        Artisan::call('cache:clear');

        flash(translate("Settings updated successfully"))->success();
        return back();
    }

    public function google_firebase_update(Request $request)
    {
        $business_settings = BusinessSetting::where('type', 'google_firebase')->first();

        if ($request->has('google_firebase')) {
            $business_settings->value = 1;
            $business_settings->save();
        } else {
            $business_settings->value = 0;
            $business_settings->save();
        }

        Artisan::call('cache:clear');

        flash(translate("Settings updated successfully"))->success();
        return back();
    }


    /**
     * Update the API key's for GOOGLE analytics.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function whatsappChatUpdate(Request $request)
    {
        foreach ($request->types as $key => $type) {
            $this->overWriteEnvFile($type, $request[$type]);
        }

        $business_settings = BusinessSetting::where('type', 'whatsapp_chat')->first();

        if ($request->has('whatsapp_chat')) {
            $business_settings->value = 1;
            $business_settings->save();
        } else {
            $business_settings->value = 0;
            $business_settings->save();
        }

        Artisan::call('cache:clear');

        flash(translate("Settings updated successfully"))->success();
        return back();
    }

    public function facebook_comment_update(Request $request)
    {
        foreach ($request->types as $key => $type) {
            $this->overWriteEnvFile($type, $request[$type]);
        }

        $business_settings = BusinessSetting::where('type', 'facebook_comment')->first();
        if (!$business_settings) {
            $business_settings = new BusinessSetting;
            $business_settings->type = 'facebook_comment';
        }

        $business_settings->value = 0;
        if ($request->facebook_comment) {
            $business_settings->value = 1;
        }

        $business_settings->save();

        Artisan::call('cache:clear');

        flash(translate("Settings updated successfully"))->success();
        return back();
    }

    public function facebook_pixel_update(Request $request)
    {
        foreach ($request->types as $key => $type) {
            $this->overWriteEnvFile($type, $request[$type]);
        }

        $business_settings = BusinessSetting::where('type', 'facebook_pixel')->first();

        if ($request->has('facebook_pixel')) {
            $business_settings->value = 1;
            $business_settings->save();
        } else {
            $business_settings->value = 0;
            $business_settings->save();
        }

        Artisan::call('cache:clear');

        flash(translate("Settings updated successfully"))->success();
        return back();
    }

    /**
     * Update the API key's for other methods.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function env_key_update(Request $request)
    {
        foreach ($request->types as $key => $type) {
            $this->overWriteEnvFile($type, $request[$type]);
        }

        flash(translate("Settings updated successfully"))->success();
        return back();
    }

    /**
     * overWrite the Env File values.
     * @param  String type
     * @param  String value
     * @return \Illuminate\Http\Response
     */
    public function overWriteEnvFile($type, $val)
    {
        if (env('DEMO_MODE') != 'On') {
            $path = base_path('.env');
            if (file_exists($path)) {
                $val = '"' . trim($val) . '"';
                if (is_numeric(strpos(file_get_contents($path), $type)) && strpos(file_get_contents($path), $type) >= 0) {
                    file_put_contents($path, str_replace(
                        $type . '="' . env($type) . '"',
                        $type . '=' . $val,
                        file_get_contents($path)
                    ));
                } else {
                    file_put_contents($path, file_get_contents($path) . "\r\n" . $type . '=' . $val);
                }
            }
        }
    }

    public function seller_verification_form(Request $request)
    {
        return view('backend.sellers.seller_verification_form.index');
    }

    /**
     * Update sell verification form.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function seller_verification_form_update(Request $request)
    {
        $form = array();
        $select_types = ['select', 'multi_select', 'radio'];
        $j = 0;
        for ($i = 0; $i < count($request->type); $i++) {
            $item['type'] = $request->type[$i];
            $item['label'] = $request->label[$i];
            if (in_array($request->type[$i], $select_types)) {
                $item['options'] = json_encode($request['options_' . $request->option[$j]]);
                $j++;
            }
            array_push($form, $item);
        }
        $business_settings = BusinessSetting::where('type', 'verification_form')->first();
        $business_settings->value = json_encode($form);
        if ($business_settings->save()) {
            Artisan::call('cache:clear');

            flash(translate("Verification form updated successfully"))->success();
            return back();
        }
    }

    public function update(Request $request)
    {
        foreach ($request->types as $key => $type) {
            if ($type == 'site_name') {
                $this->overWriteEnvFile('APP_NAME', $request[$type]);
            }
            if ($type == 'timezone') {
                $this->overWriteEnvFile('APP_TIMEZONE', $request[$type]);
            } else {
                $lang = null;
                if (gettype($type) == 'array') {
                    $lang = array_key_first($type);
                    $type = $type[$lang];
                    $business_settings = BusinessSetting::where('type', $type)->where('lang', $lang)->first();
                } else {
                    $business_settings = BusinessSetting::where('type', $type)->first();
                }

                if ($business_settings != null) {
                    if (gettype($request[$type]) == 'array') {
                        $business_settings->value = json_encode($request[$type]);
                    } else {
                        $business_settings->value = $request[$type];
                        if ($type == "seller_commission_type"  && $request[$type] == "category_based") {
                            $business_settings2 = BusinessSetting::where('type', 'category_wise_commission')->first();
                            $business_settings2->value = 1;
                            $business_settings2->save();
                        } elseif ($type == "seller_commission_type" && ($request[$type] == "seller_based" || $request[$type] == "fixed_rate")) {
                            $business_settings2 = BusinessSetting::where('type', 'category_wise_commission')->first();
                            $business_settings2->value = 0;
                            $business_settings2->save();
                        }
                    }
                    $business_settings->lang = $lang;
                    $business_settings->save();
                } else {
                    $business_settings = new BusinessSetting;
                    $business_settings->type = $type;
                    if (gettype($request[$type]) == 'array') {
                        $business_settings->value = json_encode($request[$type]);
                    } else {
                        $business_settings->value = $request[$type];
                    }
                    $business_settings->lang = $lang;
                    $business_settings->save();
                }
            }
        }

        Artisan::call('cache:clear');
        Redis::flushAll();

        flash(translate("Settings updated successfully"))->success();
        // If the request from a tabs with tab input
        if ($request->has('tab')) {
            return Redirect::to(URL::previous() . "#" . $request->tab);
        }
        return redirect()->back();
    }

    public function updateActivationSettings(Request $request)
    {
        $env_changes = ['FORCE_HTTPS', 'FILESYSTEM_DRIVER'];
        if (in_array($request->type, $env_changes)) {

            return $this->updateActivationSettingsInEnv($request);
        }

        $business_settings = BusinessSetting::where('type', $request->type)->first();
        if ($business_settings != null) {
            if ($request->type == 'maintenance_mode' && $request->value == '1') {
                if (env('DEMO_MODE') != 'On') {
                    Artisan::call('down');
                }
            } elseif ($request->type == 'maintenance_mode' && $request->value == '0') {
                if (env('DEMO_MODE') != 'On') {
                    Artisan::call('up');
                }
            }
            $business_settings->value = $request->value;
            $business_settings->save();
        } else {
            $business_settings = new BusinessSetting;
            $business_settings->type = $request->type;
            $business_settings->value = $request->value;
            $business_settings->save();
        }

        Artisan::call('cache:clear');
        return 1;
    }

    public function updatePaymentActivationSettings(Request $request)
    {
        $payment_method = PaymentMethod::findOrFail($request->id);
        $payment_method->active = $request->value;
        $payment_method->save();

        Artisan::call('cache:clear');
        return 1;
    }

    public function updateActivationSettingsInEnv($request)
    {
        if ($request->type == 'FORCE_HTTPS' && $request->value == '1') {
            $this->overWriteEnvFile($request->type, 'On');

            if (strpos(env('APP_URL'), 'http:') !== FALSE) {
                $this->overWriteEnvFile('APP_URL', str_replace("http:", "https:", env('APP_URL')));
            }
        } elseif ($request->type == 'FORCE_HTTPS' && $request->value == '0') {
            $this->overWriteEnvFile($request->type, 'Off');
            if (strpos(env('APP_URL'), 'https:') !== FALSE) {
                $this->overWriteEnvFile('APP_URL', str_replace("https:", "http:", env('APP_URL')));
            }
        } elseif ($request->type == 'FILESYSTEM_DRIVER') {
            $this->overWriteEnvFile($request->type, $request->value);
        }

        return 1;
    }

    public function vendor_commission(Request $request)
    {
        return view('backend.sellers.seller_commission.index');
    }

    public function shipping_configuration(Request $request)
    {
        return view('backend.setup_configurations.shipping_configuration.index');
    }

    public function shipping_configuration_update(Request $request)
    {
        if ($request->type == 'shipping_type' && $request->shipping_type == 'carrier_wise_shipping') {
            $inactiveZoneIds = Zone::where('status', 0)->pluck('id')->toArray();
            $hasInvalidCountries = Country::where('status', 1)
                ->where(function ($query) use ($inactiveZoneIds) {
                    $query->where('zone_id', 0)
                        ->orWhereIn('zone_id', $inactiveZoneIds);
                })
                ->exists();

            if ($hasInvalidCountries) {
                flash(translate('Your active shipping countries are assigned to inactive or undefined shipping zones. Please review your zone setup before enabling carrier-wise shipping.'))->error();
                return back();
            }
        }
        $business_settings = BusinessSetting::where('type', $request->type)->first();
        $business_settings->value = $request[$request->type];

        $business_settings->save();

        Artisan::call('cache:clear');
        flash(translate('Shipping Method updated successfully'))->success();
        return back();
    }

    public function order_configuration()
    {
        return view('backend.setup_configurations.order_configuration.index');
    }


    public function import_data(Request $request)
    {
        if (env("DEMO_MODE") == "On") {
            flash(translate('Demo data import will not work in demo site'))->error();
            return back();
        }

        if (! AddonController::isLocalhostDomain()) {

            $check_domain_verification =  AddonController::checkVerification('item', $request->purchase_key);
            $check_domain_activation =  AddonController::checkActivation('item', $request->purchase_key);

            if (!$check_domain_verification || !$check_domain_activation) {
                return translate('Please activate your domain at first');
            }
        }

        // import sql
        $sql_path = base_path('public/demo.sql');
        DB::unprepared(file_get_contents($sql_path));

        // extract images
        $zip = new ZipArchive;
        $zip->open(base_path('public/uploads.zip'));
        $zip->extractTo('public/uploads/all/');
        flash(translate('Demo data uploaded successfully'))->success();
        return redirect()->back();
    }
}
