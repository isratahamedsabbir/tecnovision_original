<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForgetPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {

        try {
            $email = $request->input('email');
            $user  = User::where('email', $email)->first();
            $otp   = rand(1000, 9999);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }
                
            Mail::to($email)->send(new OtpMail($otp, $user, 'Reset Your Password'));

            $user->otp            = $otp;
            $user->otp_expires_at = Carbon::now()->addMinutes(5);
            $user->save();

            return response()->json(['success' => true, 'message' => 'OTP sent to your email address'], 200);
            
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong' . $e], 500);
        }
    }

    public function ResetPassword(Request $request)
    {
        try {
            $email           = $request->input('email');
            $otp             = $request->input('otp');
            $password        = $request->input('password');
            $password_confirmation = $request->input('password_confirmation');

            if ($password !== $password_confirmation) {
                return response()->json(['success' => false, 'message' => 'Password and Confirm Password do not match'], 400);
            }

            $user = User::where('email', $email)->first();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            if ($user->otp != null && $user->otp == $otp && $user->otp_expires_at != null && Carbon::now()->lessThanOrEqualTo($user->otp_expires_at)) {

                $user->password = Hash::make($password);
                $user->otp = null;
                $user->otp_expires_at = null;

                $user->save();

                return response()->json(['success' => true, 'message' => 'Password has been reset successfully'], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Invalid or expired token'], 400);
            }
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong' . $e], 500);
        }
    }
}
