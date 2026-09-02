<?php

namespace App\Http\Controllers;

use App\Models\Social;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Socialite;

class SocialController extends Controller
{
    public $route = 'admin.social';
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Social::query()->orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('icon', function ($data) {
                    $url = asset($data->icon && file_exists(public_path($data->icon)) ? $data->icon : 'default/logo.svg');
                    return '<img src="' . $url . '" alt="image" style="width: 50px; max-height: 50px; margin-left: 20px;">';
                })
                ->addColumn('status', function ($data) {

                    $statusRoute = route($this->route . '.status', $data->id);

                    $status = ' <div class="form-check form-switch mt-2">';
                    $status .= ' <input onclick="showStatusChangeAlert(\'' . $statusRoute . '\')" type="checkbox" class="form-check-input d-none" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status" ' . ($data->status == 1 ? 'checked' : '') . '>';
                    $status .= '<label for="customSwitch' . $data->id . '" class="form-check-label">';
                    $status .= "<img style='width: 30px;' src='" . asset($data->status == 1 ? 'default/on.png' : 'default/off.png') . "'>";
                    $status .= '</label></div>';
                    return $status;
                })
                ->addColumn('action', function ($data) {

                    $editRoute = route($this->route . '.edit', $data->id);
                    $showRoute = route($this->route . '.show', $data->id);
                    $deleteRoute = route($this->route . '.destroy', $data->id);

                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">

                                <a href="#" type="button" onclick="goToEdit(\'' . $editRoute . '\')" class="btn btn-primary fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-edit"></i>
                                </a>

                                <a href="#" type="button" onclick="goToOpen(\'' . $showRoute . '\')" class="btn btn-success fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-eye"></i>
                                </a>

                                <a href="#" type="button" onclick="showDeleteConfirm(\'' . $deleteRoute . '\')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-trash"></i>
                                </a>
                            </div>';
                })
                ->rawColumns(['icon', 'status', 'action'])
                ->make();
        }
        
        return view("backend.social.index", [
            'route' => $this->route
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $socials = Social::where('status', 'active')->get();
        return view('backend.layouts.social.create', compact('socials'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'             => 'required|max:250',
            'content'           => 'required|string',
            'thumbnail'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'category_id'       => 'required|exists:socials,id',
            'subcategory_id'    => 'required|exists:subcategories,id',
            'images'            => 'nullable|array|max:3',
            'images.*'          => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $social = new Social();

            $social->user_id = auth('web')->user()->id;

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = fileUpload($request->file('thumbnail'), 'social', time() . '_' . getFileName($request->file('thumbnail')));
            }

            $social->title = $data['title'];
            $social->thumbnail = $data['thumbnail'];
            $social->content = $data['content'];
            $social->category_id = $data['category_id'];
            $social->subcategory_id = $data['subcategory_id'];
            $social->save();

            session()->put('success', 'social created successfully');
        } catch (Exception $e) {

            session()->put('error', $e->getMessage());
        }

        return redirect()->route('admin.social.index')->with('success', 'social created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Social $social, $id)
    {
        $social = Social::with(['category', 'subcategory', 'user'])->where('id', $id)->first();
        return view('backend.layouts.social.show', compact('social'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Social $social, $id)
    {
        $social = Social::findOrFail($id);
        return view('backend.layouts.social.edit', compact('social'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title'             => 'required|max:250',
            'content'           => 'required|string',
            'thumbnail'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'category_id'       => 'required|exists:socials,id',
            'subcategory_id'    => 'required|exists:subcategories,id',
            'images'            => 'nullable|array|max:3',
            'images.*'          => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $social = Social::findOrFail($id);

            if ($request->hasFile('thumbnail')) {
                $validate['thumbnail'] = fileUpload($request->file('thumbnail'), 'social', time() . '_' . getFileName($request->file('thumbnail')));
            }

            $social->title = $data['title'];
            $social->thumbnail = $data['thumbnail'] ?? $social->thumbnail;
            $social->content = $data['content'];
            $social->category_id = $data['category_id'];
            $social->subcategory_id = $data['subcategory_id'];
            $social->save();

            session()->put('success', 'social updated successfully');
        } catch (Exception $e) {

            session()->put('error', $e->getMessage());
        }

        return redirect()->route('admin.social.edit', $social->id)->with('success', 'social updated successfully');
    }

    public function destroy(string $id)
    {
        try {

            $data = Social::findOrFail($id);

            if ($data->thumbnail && file_exists(public_path($data->thumbnail))) {
                fileDelete(public_path($data->thumbnail));
            }

            $data->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Your action was successful!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your action was successful!'
            ]);
        }
    }

    public function status(int $id): JsonResponse
    {
        $data = Social::findOrFail($id);
        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item not found.',
            ]);
        }
        $data->status = $data->status === 1 ? 0 : 1;
        $data->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Your action was successful!',
        ]);
    }


    public function RedirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }
    
}
