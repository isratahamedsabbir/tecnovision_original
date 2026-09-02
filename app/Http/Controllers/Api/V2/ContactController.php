<?php

namespace App\Http\Controllers\Api\V2;

use App\Mail\ContactMailManager;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Log;
use Mail;

class ContactController extends Controller
{
    public function __construct()
    {
        // Staff Permission Check
        /* $this->middleware(['permission:view_all_contacts'])->only('index');
        $this->middleware(['permission:reply_to_contact'])->only('reply_modal'); */
    }

    public function contact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'phone'    => ['required', 'regex:/^\+?[0-9]{7,15}$/'],
            'content'  => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        $admin = get_admin();
        $adminEmail = $admin->email ?? null;

        $array = [];

        $array['name']    = $request->name;
        $array['email']   = $request->email;
        $array['phone']   = $request->phone;
        $array['content'] = str_replace("\n", "<br>", $request->content);
        $array['subject'] = translate('Query Contact');
        $array['from']    = $request->email;

        try {
            Mail::to(get_setting('contact_email') ?: $adminEmail)->queue(new ContactMailManager($array));
            Contact::insert([
                'name'    => $request->name,
                'email'   => $request->email,
                'phone'   => $request->phone,
                'content' => $request->content,
            ]);
            return response()->json(['success' => true, 'message' => translate('Query has been sent successfully')]);
        } catch (\Throwable $e) {
            Log::error('Contact API failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['success' => false, 'message' => translate('Something Went wrong')]);
        }
    }
}
