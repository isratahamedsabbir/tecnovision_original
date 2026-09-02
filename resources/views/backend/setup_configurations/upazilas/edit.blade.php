@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{translate('Upazila Information')}}</h5>
</div>

<div class="row">
  <div class="col-lg-8 mx-auto">
      <div class="card">
          <div class="card-body p-0">
              <form class="p-4" action="{{ route('upazilas.update', $upazila->id) }}" method="POST" enctype="multipart/form-data">
                  <input name="_method" type="hidden" value="PATCH">
                  @csrf

                  <div class="form-group mb-3">
                      <label for="name">Name</label>
                      <input type="text" placeholder="" value="{{ $upazila->name }}" name="name" class="form-control" required>
                  </div>

                  <div class="form-group">
                        <label for="state_id">District</label>
                        <select class="select2 form-control aiz-selectpicker" name="district_id" data-selected="{{ $upazila->district_id }}" data-toggle="select2" data-placeholder="Choose ..." data-live-search="true">
                            @foreach (\App\Models\District::where('status', 1)->get() as $district)
                                <option value="{{ $district->id }}">
                                    {{ $district->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                  <div class="form-group mb-3 text-right">
                      <button type="submit" class="btn btn-primary">{{translate('Update')}}</button>
                  </div>
              </form>
          </div>
      </div>
  </div>
</div>

@endsection
