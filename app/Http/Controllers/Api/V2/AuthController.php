<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\OTPVerificationController;
use App\Mail\GuestAccountOpeningMailManager;
use App\Models\Address;
use App\Models\BusinessSetting;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\AppEmailVerificationNotification;
use Hash;
use Socialite;
use App\Models\Cart;
use App\Models\District;
use App\Models\SignupAttempt;
use App\Models\Upazila;
use App\Rules\Recaptcha;
use App\Utility\EmailUtility;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\PersonalAccessToken;
use Log;
use Mail;
use Cache;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $messages = array(
            'name.required' => translate('Name is required'),
            'email.email' => translate('Email must be a valid email address'),
            'email.unique' => translate('Email already exists'),
            'phone.required' => translate('Phone is required'),
            'phone.numeric' => translate('Phone must be a number.'),
            'phone.unique' => translate('Phone already exists'),
            //'phone.regex' => translate('Phone number must be a valid Bangladeshi number'),
            'password.required' => translate('Password is required'),
            'password.confirmed' => translate('Password confirmation does not match'),
            'password.min' => translate('Minimum 6 digits required for password')
        );

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'required|min:6|confirmed',
            'email' => 'nullable|email|unique:users,email',
            //'phone' => 'required|numeric|unique:users,phone|regex:/^\+8801[3-9][0-9]{8}$/',
            'phone' => 'required|numeric|unique:users,phone',
            'g-recaptcha-response' => [
                Rule::when(get_setting('google_recaptcha') == 1, ['required', new Recaptcha()], ['sometimes'])
            ]
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'code' => 200,
                'status' => 'error',
                'message' => $validator->errors()->all()]);
        }

        //User Create
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email ?? null;
        $user->phone = $request->phone;
        $user->password = bcrypt($request->password);
        $user->email_verified_at = date('Y-m-d H:m:s');
        //$user->verification_code = rand(100000, 999999);
        $user->save();


        /* $user->email_verified_at = null;

        if ($user->email != null) {
            if (BusinessSetting::where('type', 'email_verification')->first()->value != 1) {
                $user->email_verified_at = date('Y-m-d H:m:s');
            }
        }

        if ($user->email_verified_at == null) {
            if ($request->register_by == 'email') {
                try {
                    $user->notify(new AppEmailVerificationNotification());
                } catch (Exception $e) {
                    Log::info($e->getMessage());
                }
            } else {
                $otpController = new OTPVerificationController();
                $otpController->send_code($user);
            }
        }

        $user->save();*/
        //create token
        $user->createToken('tokens')->plainTextToken;

        $tempUserId = $request->has('temp_user_id') ? $request->temp_user_id : null;
        return $this->loginSuccess($user, '', $tempUserId);
    }

    public function resendCode()
    {
        $user = auth()->user();
        $user->verification_code = rand(100000, 999999);

        if ($user->email) {
            try {
                $user->notify(new AppEmailVerificationNotification());
            } catch (Exception $e) {
                Log::info($e->getMessage());
            }
        } else {
            $otpController = new OTPVerificationController();
            $otpController->send_code($user);
        }

        $user->save();

        return response()->json([
            'result' => true,
            'message' => translate('Verification code is sent again'),
        ], 200);
    }

    public function confirmCode(Request $request)
    {
        $user = auth()->user();

        if ($user->verification_code == $request->verification_code) {
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->verification_code = null;
            $user->save();
            return response()->json([
                'result' => true,
                'message' => translate('Your account is now verified'),
            ], 200);
        } else {
            return response()->json([
                'result' => false,
                'message' => translate('Code does not match, you can request for resending the code'),
            ], 200);
        }
    }

    public function login(Request $request)
    {
        $messages = array(
            'password.required' => translate('Password is required'),
        );

        $validator = Validator::make($request->all(), [
            'password'  => 'required',
            'email'     => 'nullable|email|required_without:phone',
            'phone'     => 'nullable|string|required_without:email'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors()->all()
            ]);
        }

        $email = $request->email ?? null;
        $phone = $request->phone ?? null;
        $req_email = $email ?? $phone;

        $user = User::whereIn('user_type', ['customer'])
            ->where(function ($query) use ($req_email) {
                $query->where('email', $req_email)->orWhere('phone', $req_email);
            })
            ->first();

        if ($user != null) {
            if (!$user->banned) {

                if (Hash::check($request->password, $user->password)) {
                    $tempUserId = $request->has('temp_user_id') ? $request->temp_user_id : null;
                    return $this->loginSuccess($user, '', $tempUserId);
                } else {
                    return response()->json(['result' => false, 'message' => translate('Unauthorized'), 'user' => null], 401);
                }

            } else {
                return response()->json(['result' => false, 'message' => translate('User is banned'), 'user' => null], 401);
            }
        } else {
            return response()->json(['result' => false, 'message' => translate('User not found'), 'user' => null], 401);
        }
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $user = request()->user();
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();

        return response()->json([
            'result' => true,
            'message' => translate('Successfully logged out')
        ]);
    }

    public function socialLogin(Request $request)
    {
        if (!$request->provider_id) {
            return response()->json([
                'result' => false,
                'message' => translate('User not found'),
                'user' => null
            ]);
        }

        switch ($request->social_provider) {
            case 'facebook':
                $social_user = Socialite::driver('facebook')->fields([
                    'name',
                    'first_name',
                    'last_name',
                    'email'
                ]);
                break;
            case 'google':
                $social_user = Socialite::driver('google')
                    ->scopes(['profile', 'email']);
                break;
            case 'twitter':
                $social_user = Socialite::driver('twitter');
                break;
            case 'apple':
                $social_user = Socialite::driver('sign-in-with-apple')
                    ->scopes(['name', 'email']);
                break;
            default:
                $social_user = null;
        }
        if ($social_user == null) {
            return response()->json(['result' => false, 'message' => translate('No social provider matches'), 'user' => null]);
        }

        if ($request->social_provider == 'twitter') {
            $social_user_details = $social_user->userFromTokenAndSecret($request->access_token, $request->secret_token);
        } else {
            $social_user_details = $social_user->userFromToken($request->access_token);
        }

        if ($social_user_details == null) {
            return response()->json(['result' => false, 'message' => translate('No social account matches'), 'user' => null]);
        }

        $existingUserByProviderId = User::where('provider_id', $request->provider_id)->first();

        if ($existingUserByProviderId) {
            $existingUserByProviderId->access_token = $social_user_details->token;
            if ($request->social_provider == 'apple') {
                $existingUserByProviderId->refresh_token = $social_user_details->refreshToken;
                if (!isset($social_user->user['is_private_email'])) {
                    $existingUserByProviderId->email = $social_user_details->email;
                }
            }
            $existingUserByProviderId->save();
            return $this->loginSuccess($existingUserByProviderId);
        } else {
            $existing_or_new_user = User::firstOrNew(
                [['email', '!=', null], 'email' => $social_user_details->email]
            );

            // $existing_or_new_user->user_type = 'customer';
            $existing_or_new_user->provider_id = $social_user_details->id;

            if (!$existing_or_new_user->exists) {
                if ($request->social_provider == 'apple') {
                    if ($request->name) {
                        $existing_or_new_user->name = $request->name;
                    } else {
                        $existing_or_new_user->name = 'Apple User';
                    }
                } else {
                    $existing_or_new_user->name = $social_user_details->name;
                }

                $existing_or_new_user->provider = $request->social_provider;
                $existing_or_new_user->email = $social_user_details->email;
                $existing_or_new_user->email_verified_at = date('Y-m-d H:m:s');
                $existing_or_new_user->password = Hash::make(uniqid());
            }

            $existing_or_new_user->save();

            return $this->loginSuccess($existing_or_new_user);
        }
    }

    // Guest user Account Create
    public function guestUserAccountCreate(Request $request)
    {
        $success = 1;
        $password = substr(hash('sha512', rand()), 0, 8);
        $isEmailVerificationEnabled = get_setting('email_verification');

        // User Create
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = addon_is_activated('otp_system') ? $request->phone : null;
        $user->password = Hash::make($password);
        $user->email_verified_at = $isEmailVerificationEnabled != 1 ? date('Y-m-d H:m:s') : null;
        $user->save();

        // Account Opening and verification(if activated) eamil send
        try {
            EmailUtility::customer_registration_email('registration_from_system_email_to_customer', $user, $password);
        } catch (\Exception $e) {
            $success = 0;
            $user->delete();
        }

        if ($success == 0) {
            return response()->json([
                'result' => false,
                'message' => translate('Something went wrong!')
            ]);
        }

        if ($isEmailVerificationEnabled == 1) {
            $user->notify(new AppEmailVerificationNotification());
        }

        // User Address Create
        $address = new Address();
        $address->user_id       = $user->id;
        $address->address       = $request->address;
        $address->country_id    = $request->country_id;
        $address->state_id      = $request->state_id;
        $address->city_id       = $request->city_id;
        $address->postal_code   = $request->postal_code;
        $address->phone         = $request->phone;
        $address->longitude     = $request->longitude;
        $address->latitude      = $request->latitude;
        $address->save();

        Cart::where('temp_user_id', $request->temp_user_id)
            ->update([
                'user_id' => $user->id,
                'temp_user_id' => null,
                'address_id' => $address->id
            ]);

        //create token
        $user->createToken('tokens')->plainTextToken;

        return $this->loginSuccess($user);
    }

    public function loginSuccess($user, $token = null, $tempUserId = null)
    {

        if (!$token) {
            $token = $user->createToken('API Token')->plainTextToken;
        }

        if ($tempUserId != null) {
            Cart::where('temp_user_id', $tempUserId)
                ->update([
                    'user_id' => $user->id,
                    'temp_user_id' => null
                ]);
        }

        return response()->json([
            'result' => true,
            'message' => translate('Successfully logged in'),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => null,
            'user' => [
                'id' => $user->id,
                'type' => $user->user_type,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'avatar_original' => uploaded_asset($user->avatar_original),
                'phone' => $user->phone,
                'email_verified' => $user->email_verified_at != null
            ]
        ]);
    }


    protected function loginFailed()
    {

        return response()->json([
            'result' => false,
            'message' => translate('Login Failed'),
            'access_token' => '',
            'token_type' => '',
            'expires_at' => null,
            'user' => [
                'id' => 0,
                'type' => '',
                'name' => '',
                'email' => '',
                'avatar' => '',
                'avatar_original' => '',
                'phone' => ''
            ]
        ]);
    }


    public function account_deletion()
    {
        if (auth()->user()) {
            Cart::where('user_id', auth()->user()->id)->delete();
        }
        $auth_user = auth()->user();
        $auth_user->tokens()->where('id', $auth_user->currentAccessToken()->id)->delete();
        $auth_user->customer_products()->delete();

        User::destroy(auth()->user()->id);

        return response()->json([
            "result" => true,
            "message" => translate('Your account deletion successfully done')
        ]);
    }

    public function getUserInfoByAccessToken(Request $request)
    {
        $token = PersonalAccessToken::findToken($request->access_token);
        if (!$token) {
            return $this->loginFailed();
        }
        $user = $token->tokenable;

        if ($user == null) {
            return $this->loginFailed();
        }

        return $this->loginSuccess($user, $request->access_token);
    }

    public function getDistricts()
    {
        if(Cache::has('districts')) {
            $districts = Cache::get('districts');
        }else{
            $districts = District::select('id', 'name')->where('status', 1)->orderBy('name', 'asc')->get();
        }
        return response()->json([
            'result' => true,
            'message' => translate('Districts retrieved successfully'),
            'districts' => $districts
        ]);
    }

    public function getUpazilas($district_id)
    {
        if(Cache::has('upazilas')) {
            $upazilas = Cache::get('upazilas');
        }else{
            $upazilas = Upazila::select('id', 'name')->orderBy('name', 'asc')->where('status', 1);
        }
        if ($district_id != null){
            $upazilas = $upazilas->where('district_id', $district_id);
        }
        $upazilas = $upazilas->get();
        return response()->json([
            'result' => true,
            'message' => translate('Upazilas retrieved successfully'),
            'upazilas' => $upazilas
        ]);
    }
}
