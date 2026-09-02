@if($detailedProduct->faqs->count() > 0)
    <div class="bg-white border mt-4 mb-4">
        <div class="p-3 p-sm-4 border-bottom">
            <h3 class="fs-16 fw-700 mb-0">
                <span>{{ translate('Product FAQs') }} ({{ $detailedProduct->faqs->count() }})</span>
            </h3>
        </div>

        <div class="px-3 px-sm-4 py-3">
            <div class="accordion" id="productFaqAccordion">
                @foreach($detailedProduct->faqs as $faq)
                    <div class="card mb-2">
                        <div class="card-header bg-white" id="faq-heading-{{ $faq->id }}">
                            <button class="btn btn-link btn-block text-left px-0 fw-600 text-reset" type="button" data-toggle="collapse" data-target="#faq-collapse-{{ $faq->id }}" aria-expanded="false" aria-controls="faq-collapse-{{ $faq->id }}">
                                {{ $faq->getTranslation('question') }}
                            </button>
                        </div>
                        <div id="faq-collapse-{{ $faq->id }}" class="collapse" aria-labelledby="faq-heading-{{ $faq->id }}" data-parent="#productFaqAccordion">
                            <div class="card-body">
                                {!! nl2br(e($faq->getTranslation('answer'))) !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
