<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OTPVerificationController extends Controller
{
    public function verification()
    {
        // Logic for showing the verification page
    }

    public function verify_phone(Request $request)
    {
        // Logic for verifying the phone number
    }

    public function resend_verificcation_code(Request $request)
    {
        // Logic for resending the verification code    
    }
    public function show_reset_password_form()
    {
        // Logic for showing the reset password form
    }

    public function reset_password_with_code(Request $request)
    {
        
    }
}