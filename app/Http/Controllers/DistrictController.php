<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\District;

class DistrictController extends Controller
{

    public function index(Request $request)
    {
        $query = District::query();
        $districts = $query->orderBy('id', 'desc')->paginate(15);
        return view('backend.setup_configurations.districts.index', compact('districts'));
    }


    public function store(Request $request)
    {
        $district = new District;
        $district->name = $request->name;
        $district->save();
        flash(translate('inserted successfully'))->success();
        return back();
    }


     public function edit(Request $request, $id)
     {
         $district  = District::findOrFail($id);
         return view('backend.setup_configurations.districts.edit', compact('district'));
     }

    public function update(Request $request, $id)
    {
        $district = District::findOrFail($id);
        $district->name = $request->name;
        $district->save();
        flash(translate('updated successfully'))->success();
        return back();
    }

    public function destroy($id)
    {
        District::findOrFail($id);
        District::destroy($id);
        flash(translate('deleted successfully'))->success();
        return redirect()->route('districts.index');
    }

    public function updateStatus(Request $request){
        $district = District::findOrFail($request->id);
        $district->status = $request->status;
        $district->save();
        return 1;
    }
}
