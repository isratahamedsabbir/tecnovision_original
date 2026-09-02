<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Filter;
use App\Models\Product;

class FilterController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:view_product_filters'])->only('index');
        $this->middleware(['permission:add_product_filter'])->only('create');
        $this->middleware(['permission:edit_product_filter'])->only('edit');
        $this->middleware(['permission:delete_product_filter'])->only('destroy');
    }

    public function index(Request $request)
    {
        $sort_search = null;
        $filters = Filter::orderBy('id', 'desc');
        if ($request->has('search')) {
            $sort_search = $request->search;
            $filters = $filters->where('name', 'like', '%' . $sort_search . '%');
        }
        $filters = $filters->paginate(15);
        return view('backend.product.filters.index', compact('filters', 'sort_search'));
    }

    public function create()
    {
        return view('backend.product.filters.create');
    }

    public function store(Request $request)
    {
        $filter = new Filter;
        $filter->name = $request->name;
        $filter->save();
        flash(translate('Filter has been inserted successfully'))->success();
        return redirect()->route('filters.index');
    }

    public function show($id)
    {
        return redirect()->route('filters.index');
    }

    public function edit(Request $request, $id)
    {
        $filter = Filter::findOrFail($id);
        return view('backend.product.filters.edit', compact('filter'));
    }

    public function update(Request $request, $id)
    {
        $filter = Filter::findOrFail($id);
        $filter->name = $request->name;
        $filter->save();
        flash(translate('Filter has been updated successfully'))->success();
        return back();
    }

    public function destroy($id)
    {
        $filter = Filter::findOrFail($id);

        $products = Product::whereJsonContains('filters', $filter->id)->get();

        if ($products->count() > 0) {
            flash(translate('This filter is used in some products. So you can not delete this filter'))->error();
            return back();
        }

        $filter->delete();
        flash(translate('Filter has been deleted successfully'))->success();
        return redirect()->route('filters.index');
    }
    
}
