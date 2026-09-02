@extends('backend.layouts.app')

@section('content')
<div class="page-content">
	<div class="aiz-titlebar text-left mt-2 pb-2 px-3 px-md-2rem border-bottom border-gray">
		<div class="row align-items-center">
			<div class="col">
				<h1 class="h3">{{ translate('Authpage Settings (Classic)') }}</h1>
			</div>
			{{-- <div class="col text-right">
					<a class="btn has-transition btn-xs p-0 hov-svg-danger" href="{{ route('home') }}"
			target="_blank" data-toggle="tooltip" data-placement="top" data-title="{{ translate('View Tutorial Video') }}">
			<svg xmlns="http://www.w3.org/2000/svg" width="19.887" height="16" viewBox="0 0 19.887 16">
				<path id="_42fbab5a39cb8436403668a76e5a774b" data-name="42fbab5a39cb8436403668a76e5a774b" d="M18.723,8H5.5A3.333,3.333,0,0,0,2.17,11.333v9.333A3.333,3.333,0,0,0,5.5,24h13.22a3.333,3.333,0,0,0,3.333-3.333V11.333A3.333,3.333,0,0,0,18.723,8Zm-3.04,8.88-5.47,2.933a1,1,0,0,1-1.473-.88V13.067a1,1,0,0,1,1.473-.88l5.47,2.933a1,1,0,0,1,0,1.76Zm-5.61-3.257L14.5,16l-4.43,2.377Z" transform="translate(-2.17 -8)" fill="#9da3ae" />
			</svg>
			</a>
		</div> --}}
	</div>
</div>

<div class="d-sm-flex">
	<!-- page side nav -->
	<div class="page-side-nav c-scrollbar-light px-3 py-2">
		<ul class="nav nav-tabs flex-sm-column border-0" role="tablist" aria-orientation="vertical">
			<!-- Home Slider -->
			<li class="nav-item">
				<a class="nav-link" id="home-slider-tab" href="#auth"
					data-toggle="tab" data-target="#auth" type="button" role="tab" aria-controls="auth" aria-selected="true">
					{{ translate('General Settings') }}
				</a>
			</li>
		</ul>
	</div>

	<!-- tab content -->
	<div class="flex-grow-1 p-sm-3 p-lg-2rem mb-2rem mb-md-0">
		<div class="tab-content">


			<!-- Home Slider -->
			<div class="tab-pane" id="auth" role="tabpanel" aria-labelledby="home-slider-tab">
				<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
					@csrf
					<input type="hidden" name="tab" value="auth">
					<div class="bg-white p-3 p-sm-2rem">
						<div class="w-100">
							<label class="col-from-label fs-13 fw-500 mb-3">{{ translate('Admin Login Background') }}</label>
							<!-- Images -->
							<div class="form-group">
								<div class="input-group" data-toggle="aizuploader" data-type="image">
									<div class="input-group-prepend">
										<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
									</div>
									<div class="form-control file-amount">{{ translate('Choose File') }}</div>
									<input type="hidden" name="types[][{{ $lang }}]" value="admin_login_background">
									<input type="hidden" name="admin_login_background" class="selected-files" value="{{ get_setting('admin_login_background', null, $lang) }}">
								</div>
								<div class="file-preview box sm">
								</div>
							</div>
						</div>

						<div class="w-100">
							<label class="col-from-label fs-13 fw-500 mb-3">{{ translate('Admin Login Page Image') }}</label>
							<!-- Images -->
							<div class="form-group">
								<div class="input-group" data-toggle="aizuploader" data-type="image">
									<div class="input-group-prepend">
										<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
									</div>
									<div class="form-control file-amount">{{ translate('Choose File') }}</div>
									<input type="hidden" name="types[][{{ $lang }}]" value="admin_login_page_image">
									<input type="hidden" name="admin_login_page_image" class="selected-files" value="{{ get_setting('admin_login_page_image', null, $lang) }}">
								</div>
								<div class="file-preview box sm">
								</div>
							</div>
						</div>
						<!-- Save Button -->
						<div class="mt-4 text-right">
							<button type="submit" class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
						</div>
					</div>
				</form>
			</div>

		</div>
	</div>
</div>
</div>

@endsection

@section('script')
<script type="text/javascript">
	$(document).ready(function() {
		AIZ.plugins.bootstrapSelect('refresh');
	});
</script>
<script>
	$(document).ready(function() {
		var hash = document.location.hash;
		if (hash) {
			$('.nav-tabs a[href="' + hash + '"]').tab('show');
		} else {
			$('.nav-tabs a[href="#auth"]').tab('show');
		}

		// Change hash for page-reload
		$('.nav-tabs a').on('shown.bs.tab', function(e) {
			window.location.hash = e.target.hash;
		});
	});
</script>
@endsection