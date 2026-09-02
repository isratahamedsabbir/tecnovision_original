@extends('backend.layouts.app')

@section('content')

@php
    CoreComponentRepository::instantiateShopRepository();
    CoreComponentRepository::initializeCache();
@endphp

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ isset($mainProduct) ? translate('Edit') : translate('Add') }} Suggested Product</h5>
            </div>
            <div class="card-body">
                <form class="form-horizontal" action="{{ route('products.suggected-product-store') }}" method="POST" id="suggestedProductForm">
                	@csrf
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">Main Product Select</label>
                        <div class="col-md-9">
                            <select class="select2 form-control aiz-selectpicker" 
                                    name="parent_id" 
                                    id="mainProduct"
                                    data-toggle="select2" 
                                    data-placeholder="Choose ..." 
                                    data-live-search="true"
                                    required>
                                <option value="">{{ translate('Select Main Product') }}</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" 
                                        {{ isset($mainProduct) && $mainProduct->id == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">Suggested Product</label>
                        <div class="col-md-9">
                            <select class="select2 form-control aiz-selectpicker"
                                    id="suggestProduct"
                                    name="suggested_product_id[]"
                                    data-toggle="select2"
                                    data-placeholder="Choose ..."
                                    data-live-search="true"
                                    multiple
                                    required>
                                @foreach ($products as $product)
                                    @if(!isset($mainProduct) || $mainProduct->id != $product->id)
                                        <option value="{{ $product->id }}"
                                            {{ isset($suggestedProducts) && in_array($product->id, $suggestedProducts) ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('suggested_product_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            {{translate('Save')}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const mainProductSelect = $('#mainProduct');
    const suggestProductSelect = $('#suggestProduct');
    const submitBtn = $('#submitBtn');

    mainProductSelect.on('change', function () {
        let productId = $(this).val();

        // reset all options first
        $('#suggestProduct option').prop('disabled', false);

        if (!productId) {
            suggestProductSelect.val(null).trigger('change');
            return;
        }

        // disable main product from suggestion list (not remove)
        $('#suggestProduct option[value="' + productId + '"]').prop('disabled', true);

        suggestProductSelect.prop('disabled', true);

        fetch(`/admin/products/suggested/${productId}`)
            .then(res => res.json())
            .then(data => {
                suggestProductSelect.val(null);

                if (data && data.length > 0) {
                    let ids = data.map(p => p.id.toString());
                    suggestProductSelect.val(ids);
                }

                suggestProductSelect.prop('disabled', false).trigger('change');
            })
            .catch(() => {
                alert('Failed to load suggested products');
                suggestProductSelect.prop('disabled', false);
            });
    });

    $('#suggestedProductForm').on('submit', function () {
        submitBtn.prop('disabled', true)
            .html('<i class="fa fa-spinner fa-spin"></i> Saving...');
    });
});
</script>

@endsection