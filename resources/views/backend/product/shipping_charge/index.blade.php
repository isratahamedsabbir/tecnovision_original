@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">All Shipping Charges.</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ url('admin/shipping_charges/create') }}" class="btn btn-circle btn-info">
                <span>Add New Shipping Charge</span>
            </a>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header d-block d-md-flex">
        <h5 class="mb-0 h6">Blog Shipping Charges</h5>
        <form class="" id="sort_shipping_charges" action="" method="GET">
            <div class="box-inline pad-rgt pull-left">
                <div class="" style="min-width: 200px;">
                    <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type name & Enter') }}">
                </div>
            </div>
        </form>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th>{{translate('Name')}}</th>
                    <th>{{translate('Cost')}}</th>
                    <th width="10%" class="text-right">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($shipping_charges as $key => $shipping_charge)
                <tr>
                    <td>{{ ($key+1) + ($shipping_charges->currentPage() - 1)*$shipping_charges->perPage() }}</td>
                    <td>{{ $shipping_charge->name }}</td>
                    <td>{{ $shipping_charge->cost }}</td>
                    <td class="text-right">
                        <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{url('admin/shipping_charges/'.$shipping_charge->id.'/edit')}}" title="{{ translate('Edit') }}">
                            <i class="las la-edit"></i>
                        </a>
                        <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('shipping_charges.destroy', $shipping_charge->id)}}" title="{{ translate('Delete') }}">
                            <i class="las la-trash"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $shipping_charges->appends(request()->input())->links() }}
        </div>
    </div>
</div>
@endsection


@section('modal')
@include('modals.delete_modal')
@endsection

