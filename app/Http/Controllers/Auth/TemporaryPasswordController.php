<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SendSmsJob;
use App\Models\SignupAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Str;

class TemporaryPasswordController extends Controller
{
    protected function sendSms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|exists:users,phone',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['errors' => ['Phone number not found']]);
        }

        $attemptCount = SignupAttempt::where('phone', $request->phone)->where('created_at', '>=', Carbon::now()->subHours(2))->count();
        if ($attemptCount >= 2) {
            return response()->json([
                'code' => 200,
                'status' => 'error',
                'message' => 'Too many attempts, please try again later.'
            ]);
        }
        
        SignupAttempt::create(['phone' => $request->phone]);

        $temporary_password = random_int(10000000, 99999999);

        $user->forceFill([
            'password' => Hash::make($temporary_password)
        ])->save();

        $sms = 'Your temporary password is ' . $temporary_password;
        SendSmsJob::dispatch($sms, $user->phone);
        
        return response()->json(['code' => 200, 'status' => 'success', 'msg' => 'Temporary password sent successfully']);
    }

        
}
