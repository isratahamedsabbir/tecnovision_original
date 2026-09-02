<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SmsTemplateController extends Controller
{
    public function index()
    {
        
        return response()->view('errors.404', [], 404);
    }
}