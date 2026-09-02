<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShippingCharge;

class ShippingChargeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search = null;
        $shipping_charges = ShippingCharge::orderBy('name', 'asc');

        if ($request->has('search')) {
            $sort_search = $request->search;
            $shipping_charges = $shipping_charges->where('name', 'like', '%' . $sort_search . '%');
        }

        $shipping_charges = $shipping_charges->paginate(15);
        return view('backend.product.shipping_charge.index', compact('shipping_charges', 'sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $all_shipping_charges = ShippingCharge::all();
        return view('backend.product.shipping_charge.create', compact('all_shipping_charges'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|max:255',
            'cost' => 'required|numeric',
        ]);

        $shipping_charge = new ShippingCharge();

        $shipping_charge->name = $request->name;
        $shipping_charge->cost = $request->cost;

        $shipping_charge->save();


        flash(translate('Shipping charge has been created successfully'))->success();
        return redirect()->route('shipping_charges.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $shipping_charge = ShippingCharge::find($id);
        $all_shipping_charges = ShippingCharge::all();

        return view('backend.product.shipping_charge.edit',  compact('shipping_charge', 'all_shipping_charges'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:255',
            'cost' => 'required|numeric',
        ]);

        $shipping_charge = ShippingCharge::find($id);

        $shipping_charge->name = $request->name;
        $shipping_charge->cost = $request->cost;

        $shipping_charge->save();


        flash(translate('Shipping charge has been updated successfully'))->success();
        return redirect()->route('shipping_charges.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        ShippingCharge::find($id)->delete();

        return redirect('admin/shipping_charges');
    }
}
