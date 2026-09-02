@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col">
			<h1 class="h3">{{ translate('Website Pages') }}</h1>
		</div>
	</div>
</div>

<div class="card">
	@can('add_website_page')
	<div class="card-header">
		<h6 class="mb-0 fw-600">{{ translate('All Pages') }}</h6>
		<a href="{{ route('custom-pages.create') }}" class="btn btn-circle btn-info">{{ translate('Add New Page') }}</a>
	</div>
	@endcan
	<div class="card-body">
		<table class="table aiz-table mb-0">
			<thead>
				<tr>
					<th data-breakpoints="lg">#</th>
					<th>{{translate('Name')}}</th>
					<th data-breakpoints="md">{{translate('URL')}}</th>
					<th data-breakpoints="md">{{translate('Show in Footer')}}</th>
					<th class="text-right">{{translate('Actions')}}</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($page as $key => $page)
				<tr>
					<td>{{ $key+1 }}</td>
					<td><a href="{{ route('custom-pages.show_custom_page', $page->slug) }}" class="text-reset">{{ $page->getTranslation('title') }}</a></td>
					<td>{{ config('app.frontend') }}/{{ $page->slug }}</td>
					<td>
						<label class="aiz-switch aiz-switch-success mb-0">
							<input onchange="update_status(this)" value="{{ $page->id }}" type="checkbox" <?php if ($page->status == 'active') echo "checked"; ?>>
							<span class="slider round"></span>
						</label>
					</td>
					<td class="text-right">
						@can('edit_website_page')
						@if($page->type == 'home_page')
						<a href="{{route('custom-pages.edit', ['id'=>$page->slug, 'lang'=>env('DEFAULT_LANGUAGE'), 'page'=>'home'] )}}" class="btn btn-icon btn-circle btn-sm btn-soft-primary" title="Edit">
							<i class="las la-pen"></i>
						</a>
						@else
						<a href="{{route('custom-pages.edit', ['id'=>$page->slug, 'lang'=>env('DEFAULT_LANGUAGE')] )}}" class="btn btn-icon btn-circle btn-sm btn-soft-primary" title="Edit">
							<i class="las la-pen"></i>
						</a>
						@endif
						@endcan
						@if(auth()->user()->can('delete_website_page'))
						<a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{ route('custom-pages.destroy', $page->id)}} " title="{{ translate('Delete') }}">
							<i class="las la-trash"></i>
						</a>
						@endif
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>
@endsection

@section('modal')
@include('modals.delete_modal')
<script>
	function update_status(el) {
		$.post("{{ route('website.pages.status') }}", {
			_token: '{{ csrf_token() }}',
			id: el.value
		}, function(data) {
			if (data == 1) {
				AIZ.plugins.notify('success', "{{ translate('updated successfully') }}");
			} else {
				AIZ.plugins.notify('danger', "{{ translate('Something went wrong') }}");
			}
		});
	}
</script>
@endsection