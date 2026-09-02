@extends('backend.layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ translate('Product Queries') }}</h5>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0 " cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ translate('User Name') }}</th>
                    <th>{{ translate('Product Name') }}</th>
                    <th data-breakpoints="lg">{{ translate('Question') }}</th>
                    <th data-breakpoints="lg">{{ translate('Reply') }}</th>
                    <th>{{ translate('status') }}</th>
                    <th data-breakpoints="lg">{{translate('Published')}}</th>
                    <th class="text-right">{{ translate('Options') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($queries as $key => $query)
                <tr>
                    <td>{{ translate($key + 1) }}</td>
                    <td>{{ $query->user->name ?? translate('Customer Not Found') }}</td>
                    <td>{{ $query->product != null ? $query->product->getTranslation('name') : translate('Product Not Found') }}</td>
                    <td>{{ Str::limit($query->question, 100) }}</td>
                    <td>{{ Str::limit($query->reply, 100) }}</td>
                    <td>
                        <span
                            class="badge badge-inline {{ $query->reply == null ? 'badge-warning' : 'badge-success'  }}">
                            {{ $query->reply == null ? translate('Not Replied') : translate('Replied')}}
                        </span>
                    </td>

                    <td>
                        <label class="aiz-switch aiz-switch-success mb-0">
                            <input type="checkbox" onchange="update_published(this)" value="{{ $query->id }}" <?php if ($query->published == 1) echo "checked"; ?>>
                            <span></span>
                        </label>
                    </td>
                    <td class="text-right">
                        <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                            href="{{ route('product_query.show', encrypt($query->id)) }}"
                            title="{{ translate('View') }}">
                            <i class="las la-eye"></i>
                        </a>
                        <a class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                            href="#" data-href="{{ route('product_query.destroy', $query->id) }}"
                            title="{{ translate('Delete') }}">
                            <i class="las la-trash"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $queries->appends(request()->input())->links() }}
        </div>
    </div>
</div>
@endsection

@section('modal')
@include('modals.delete_modal')
@endsection

@section('script')
<script type="text/javascript">
        function update_published(el){

            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post("{{ route('product_query.published') }}", {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', "{{ translate('updated successfully') }}");
                }
                else{
                    AIZ.plugins.notify('danger', "{{ translate('Something went wrong') }}");
                }
            });
        }
    </script>
@endsection
