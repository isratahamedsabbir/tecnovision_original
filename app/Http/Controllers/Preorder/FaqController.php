<?php
namespace App\Http\Controllers\Preorder;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqTranslation;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $sort_search = $request->search;

        $faqs = Faq::doesntHave('products')->latest();

        if ($sort_search) {
            $faqs->whereHas('faq_translations', function ($query) use ($sort_search) {
                $query->where('question', 'like', '%' . $sort_search . '%')
                    ->orWhere('answer', 'like', '%' . $sort_search . '%');
            });
        }

        $faqs = $faqs->paginate(15);

        return view('preorder.backend.faqs.index', compact('faqs', 'sort_search'));
    }

    public function store(Request $request)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('Data can not change in demo mode.'))->info();
            return back();
        }

        $request->validate([
            'question' => ['required', 'string'],
            'answer' => ['required', 'string'],
        ]);

        $faq = new Faq();
        $faq->question = $request->question;
        $faq->answer = $request->answer;
        $faq->status = 1;
        $faq->save();

        FaqTranslation::updateOrCreate(
            ['faq_id' => $faq->id, 'lang' => env('DEFAULT_LANGUAGE')],
            ['question' => $request->question, 'answer' => $request->answer]
        );

        flash(translate('FAQ has been added successfully'))->success();
        return back();
    }

    public function edit(Request $request, $id)
    {
        $lang = $request->lang ?? env('DEFAULT_LANGUAGE');
        $faq = Faq::doesntHave('products')->findOrFail($id);

        return view('preorder.backend.faqs.edit', compact('faq', 'lang'));
    }

    public function update(Request $request, $id)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('Data can not change in demo mode.'))->info();
            return back();
        }

        $request->validate([
            'question' => ['required', 'string'],
            'answer' => ['required', 'string'],
        ]);

        $faq = Faq::doesntHave('products')->findOrFail($id);
        $lang = $request->lang ?? env('DEFAULT_LANGUAGE');

        if ($lang == env('DEFAULT_LANGUAGE')) {
            $faq->question = $request->question;
            $faq->answer = $request->answer;
            $faq->save();
        }

        FaqTranslation::updateOrCreate(
            ['faq_id' => $faq->id, 'lang' => $lang],
            ['question' => $request->question, 'answer' => $request->answer]
        );

        flash(translate('FAQ has been updated successfully'))->success();
        return back();
    }

    public function destroy($id)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('Data can not change in demo mode.'))->info();
            return back();
        }

        $faq = Faq::doesntHave('products')->findOrFail($id);
        $faq->faq_translations()->delete();
        $faq->delete();

        flash(translate('FAQ has been deleted successfully'))->success();
        return back();
    }

    public function updateStatus(Request $request)
    {
        $faq = Faq::doesntHave('products')->findOrFail($request->id);
        $faq->status = $request->status;

        return $faq->save() ? 1 : 0;
    }
}
