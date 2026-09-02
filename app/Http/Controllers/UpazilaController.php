<?php
namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Upazila;
use Illuminate\Http\Request;

class UpazilaController extends Controller
{

    public function index(Request $request)
    {
        $query     = Upazila::query();
        $upazilas = $query->with('district')->orderBy('id', 'desc')->paginate(15);
        return view('backend.setup_configurations.upazilas.index', compact('upazilas'));
    }

    public function store(Request $request)
    {
        $upazila = new Upazila;
        $upazila->district_id = $request->district_id;
        $upazila->name = $request->name;
        $upazila->save();
        flash(translate('inserted successfully'))->success();
        return back();
    }

    public function edit(Request $request, $id)
    {
        $upazila = Upazila::findOrFail($id);
        return view('backend.setup_configurations.upazilas.edit', compact('upazila'));
    }

    public function update(Request $request, $id)
    {
        $upazila = Upazila::findOrFail($id);
        $upazila->district_id = $request->district_id;
        $upazila->name = $request->name;
        $upazila->save();
        flash(translate('updated successfully'))->success();
        return back();
    }

    public function destroy($id)
    {
        Upazila::findOrFail($id);
        Upazila::destroy($id);
        flash(translate('deleted successfully'))->success();
        return redirect()->route('upazilas.index');
    }

    public function updateStatus(Request $request)
    {
        $upazila = Upazila::findOrFail($request->id);
        $upazila->status = $request->status;
        $upazila->save();
        return 1;
    }
}
