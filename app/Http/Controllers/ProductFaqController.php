<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\FaqTranslation;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductFaqController extends Controller
{
    public function index(Request $request)
    {
        $sort_search = $request->search;

        $faqs = Faq::with(['products', 'faq_translations'])->whereHas('products')->latest();

        if ($sort_search) {
            $faqs->where(function ($query) use ($sort_search) {
                $query->whereHas('faq_translations', function ($translationQuery) use ($sort_search) {
                    $translationQuery->where('question', 'like', '%' . $sort_search . '%')
                        ->orWhere('answer', 'like', '%' . $sort_search . '%');
                })->orWhereHas('products', function ($productQuery) use ($sort_search) {
                    $productQuery->where('name', 'like', '%' . $sort_search . '%')
                        ->orWhereHas('product_translations', function ($productTranslationQuery) use ($sort_search) {
                            $productTranslationQuery->where('name', 'like', '%' . $sort_search . '%');
                        });
                });
            });
        }

        $faqs = $faqs->paginate(10);

        return view('backend.product.product_faqs.index', compact('faqs', 'sort_search'));
    }

    public function create()
    {
        $products = filter_products(Product::select('id', 'name'))->orderBy('name')->get();
        return view('backend.product.product_faqs.create', compact('products'));
    }

    public function store(Request $request)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('Data can not change in demo mode.'))->info();
            return back();
        }

        $request->validate([
            'products' => ['required', 'array', 'min:1'],
            'products.*' => ['required', 'exists:products,id'],
            'faqs' => ['required', 'array', 'min:1'],
            'faqs.*.question' => ['required', 'string'],
            'faqs.*.answer' => ['required', 'string'],
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->faqs as $faqData) {
                if (blank($faqData['question'] ?? null) || blank($faqData['answer'] ?? null)) {
                    continue;
                }

                $faq = new Faq();
                $faq->question = $faqData['question'];
                $faq->answer = $faqData['answer'];
                $faq->status = 1;
                $faq->save();

                FaqTranslation::updateOrCreate(
                    [
                        'faq_id' => $faq->id,
                        'lang' => env('DEFAULT_LANGUAGE'),
                    ],
                    [
                        'question' => $faqData['question'],
                        'answer' => $faqData['answer'],
                    ]
                );

                $faq->products()->sync($request->products);
            }
        });

        flash(translate('Product FAQs have been added successfully'))->success();
        return redirect()->route('product-faqs.index');
    }

    public function edit($id)
    {
        $faq = Faq::with(['products', 'faq_translations'])->whereHas('products')->findOrFail($id);
        $products = filter_products(Product::select('id', 'name'))->orderBy('name')->get();
        return view('backend.product.product_faqs.edit', compact('faq', 'products'));
    }

    public function update(Request $request, $id)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('Data can not change in demo mode.'))->info();
            return back();
        }

        $request->validate([
            'products' => ['required', 'array', 'min:1'],
            'products.*' => ['required', 'exists:products,id'],
            'question' => ['required', 'string'],
            'answer' => ['required', 'string'],
        ]);

        $faq = Faq::whereHas('products')->findOrFail($id);
        $faq->question = $request->question;
        $faq->answer = $request->answer;
        $faq->save();

        FaqTranslation::updateOrCreate(
            [
                'faq_id' => $faq->id,
                'lang' => env('DEFAULT_LANGUAGE'),
            ],
            [
                'question' => $request->question,
                'answer' => $request->answer,
            ]
        );

        $faq->products()->sync($request->products);

        flash(translate('Product FAQ has been updated successfully'))->success();
        return redirect()->route('product-faqs.index');
    }

    public function destroy($id)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('Data can not change in demo mode.'))->info();
            return back();
        }

        $faq = Faq::whereHas('products')->findOrFail($id);
        $faq->products()->detach();
        $faq->faq_translations()->delete();
        $faq->delete();

        flash(translate('Product FAQ has been deleted successfully'))->success();
        return back();
    }

    public function updateStatus(Request $request)
    {
        $faq = Faq::whereHas('products')->findOrFail($request->id);
        $faq->status = $request->status;

        return $faq->save() ? 1 : 0;
    }
}
