@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h1 class="h3">{{ translate('Add Product FAQs') }}</h1>
</div>

<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('product-faqs.store') }}" method="POST">
                    @csrf

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">{{ translate('Products') }}</label>
                        <div class="col-sm-9">
                            <select name="products[]" class="form-control aiz-selectpicker" multiple required data-live-search="true" data-selected-text-format="count" data-placeholder="{{ translate('Choose Products') }}">
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" @selected(in_array($product->id, old('products', [])))>
                                        {{ $product->getTranslation('name') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('products')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div id="faq-wrapper">
                        @php
                            $oldFaqs = old('faqs', [['question' => '', 'answer' => '']]);
                        @endphp
                        @foreach($oldFaqs as $index => $oldFaq)
                            <div class="border rounded p-3 mb-3 faq-item">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">{{ translate('FAQ') }} #{{ $index + 1 }}</h6>
                                    @if($index > 0)
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-faq">{{ translate('Remove') }}</button>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>{{ translate('Question') }}</label>
                                    <input type="text" name="faqs[{{ $index }}][question]" class="form-control" value="{{ $oldFaq['question'] ?? '' }}" required>
                                </div>

                                <div class="form-group mb-0">
                                    <label>{{ translate('Answer') }}</label>
                                    <textarea name="faqs[{{ $index }}][answer]" class="form-control" rows="5" required>{{ $oldFaq['answer'] ?? '' }}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <button type="button" id="add-faq" class="btn btn-outline-primary">{{ translate('Add More FAQ') }}</button>
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

@section('script')
<script>
    $(document).ready(function(){
        let faqIndex = $('#faq-wrapper .faq-item').length;

        $('#add-faq').on('click', function(){
            const faqHtml = `
                <div class="border rounded p-3 mb-3 faq-item">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">{{ translate('FAQ') }} #${faqIndex + 1}</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-faq">{{ translate('Remove') }}</button>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Question') }}</label>
                        <input type="text" name="faqs[${faqIndex}][question]" class="form-control" required>
                    </div>
                    <div class="form-group mb-0">
                        <label>{{ translate('Answer') }}</label>
                        <textarea name="faqs[${faqIndex}][answer]" class="form-control" rows="5" required></textarea>
                    </div>
                </div>
            `;

            $('#faq-wrapper').append(faqHtml);
            faqIndex++;
        });

        $(document).on('click', '.remove-faq', function(){
            $(this).closest('.faq-item').remove();
        });
    });
</script>
@endsection
