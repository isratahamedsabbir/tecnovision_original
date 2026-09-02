<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function index()
    {
        return response()->view('errors.404', [], 404);
    }

}