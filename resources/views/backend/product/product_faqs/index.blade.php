@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{ translate('Product FAQs') }}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('product-faqs.create') }}" class="btn btn-primary">
                {{ translate('Add New FAQ') }}
            </a>
        </div>
    </div>
</div>

<div class="card">
    <form action="" method="GET" id="sort_faqs">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('All Product FAQs') }}</h5>
            </div>
            <div class="col-md-3 ml-auto">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" value="{{ $sort_search }}" placeholder="{{ translate('Type & Enter') }}">
                </div>
            </div>
        </div>

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ translate('Question') }}</th>
                        <th>{{ translate('Products') }}</th>
                        <th>{{ translate('Status') }}</th>
                        <th class="text-right">{{ translate('Options') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($faqs as $key => $faq)
                        <tr>
                            <td>{{ ($key + 1) + ($faqs->currentPage() - 1) * $faqs->perPage() }}</td>
                            <td>{{ $faq->getTranslation('question') }}</td>
                            <td>
                                <div class="d-flex flex-wrap" style="gap: 6px;">
                                    @forelse($faq->products as $product)
                                        <span>{{ $product->getTranslation('name') }}</span>
                                    @empty
                                        <span class="text-muted">{{ translate('No product found') }}</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input onchange="update_status(this)" value="{{ $faq->id }}" type="checkbox" @if($faq->status == 1) checked @endif>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('product-faqs.edit', $faq->id) }}" class="btn btn-soft-primary btn-icon btn-circle btn-sm" title="{{ translate('Edit') }}">
                                    <i class="las la-edit"></i>
                                </a>
                                <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{ route('product-faqs.destroy', $faq->id) }}" title="{{ translate('Delete') }}">
                                    <i class="las la-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination mt-3">
                {{ $faqs->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script>
        function update_status(el){
            if('{{ env('DEMO_MODE') }}' == 'On'){
                AIZ.plugins.notify('info', '{{ translate('Data can not change in demo mode.') }}');
                return;
            }

            var status = el.checked ? 1 : 0;

            $.post('{{ route('product-faqs.update-status') }}', {
                _token:'{{ csrf_token() }}',
                id: el.value,
                status: status
            }, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ translate('Product FAQ status updated successfully') }}');
                }else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }
    </script>
@endsection
