<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Faq;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::doesntHave('products')
            ->where('status', 1)
            ->orderByDesc('id')
            ->get()
            ->map(function ($faq) {
                return [
                    'id' => $faq->id,
                    'question' => $faq->getTranslation('question'),
                    'answer' => $faq->getTranslation('answer'),
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'fetched successfully',
            'data' => $faqs,
        ], 200);
    }
}
