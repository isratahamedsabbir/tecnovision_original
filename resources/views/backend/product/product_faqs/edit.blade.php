@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h1 class="h3">{{ translate('Edit Product FAQ') }}</h1>
</div>

<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('product-faqs.update', $faq->id) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">{{ translate('Products') }}</label>
                        <div class="col-sm-9">
                            <select name="products[]" class="form-control aiz-selectpicker" multiple required data-live-search="true" data-selected-text-format="count" data-placeholder="{{ translate('Choose Products') }}">
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" @selected($faq->products->contains('id', $product->id))>
                                        {{ $product->getTranslation('name') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">{{ translate('Question') }}</label>
                        <div class="col-sm-9">
                            <input type="text" name="question" class="form-control" value="{{ old('question', $faq->getTranslation('question')) }}" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">{{ translate('Answer') }}</label>
                        <div class="col-sm-9">
                            <textarea name="answer" class="form-control" rows="6" required>{{ old('answer', $faq->getTranslation('answer')) }}</textarea>
                        </div>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">{{ translate('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
