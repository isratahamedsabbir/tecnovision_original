@extends('backend.layouts.app')

@section('style')
<!-- Toastr -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />

<!-- DataTables -->
<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">

<style>
    .dataTables_filter {
        margin-bottom: 10px;
        padding-right: 10px;
        text-align: right;
    }

    .dataTables_filter input[type="search"] {
        border: 1px solid #ccc;
        border-radius: 6px;
        padding: 6px 10px;
        outline: none;
        box-shadow: none;
        width: 200px;
    }

    table.dataTable {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    .table-responsive {
        overflow-x: auto;
    }
</style>
@endsection

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('All Notes') }}</h1>
        </div>
        @can('add_note')
        <div class="col-md-6 text-md-right">
            <a href="{{ route('note.create') }}" class="btn btn-circle btn-info">
                <span>{{ translate('Add New Note') }}</span>
            </a>
        </div>
        @endcan
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <table class="table table-bordered text-nowrap border-bottom" id="datatable">
                    <thead>
                        <tr>
                            <th class="bg-transparent border-bottom-0 wp-15">ID</th>
                            <th class="bg-transparent border-bottom-0 wp-15">Name</th>
                            <th class="bg-transparent border-bottom-0">Icon</th>
                            <th class="bg-transparent border-bottom-0">Status</th>
                            <th class="bg-transparent border-bottom-0">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    $(document).ready(function() {

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            }
        });
        if (!$.fn.DataTable.isDataTable('#datatable')) {
            let dTable = $('#datatable').DataTable({
                order: [],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                processing: true,
                responsive: true,
                serverSide: true,

                language: {
                    processing: `<div class="text-center">
                        <img src="{{ asset('default/loader.gif') }}" alt="Loader" style="width: 50px;">
                        </div>`
                },

                scroller: {
                    loadingIndicator: false
                },
                pagingType: "full_numbers",
                dom: "<'row justify-content-between table-topbar'<'col-md-4 col-sm-4'l><'col-md-5 col-sm-5 px-0'f>>tipr",
                ajax: {
                    url: "{{ route($route . '.index') }}",
                    type: "GET",
                },

                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'icon',
                        name: 'icon',
                        orderable: true,
                        searchable: true,
                        className: 'dt-center text-center'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false,
                        className: 'dt-center text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'dt-center text-center'
                    },
                ],
            });
        }
    });

    // Status Change Confirm Alert
    function showStatusChangeAlert(route) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to update the status?',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
        }).then((result) => {
            if (result.isConfirmed) {
                NProgress.start();
                $.ajax({
                    type: "GET",
                    url: route,
                    success: function(resp) {
                        NProgress.done();
                        toastr.success(resp.message);
                        $('#datatable').DataTable().ajax.reload();
                    },
                    error: function(error) {
                        NProgress.done();
                        toastr.error(error.message);
                    }
                });
            }
        });
    }

    // delete Confirm
    function showDeleteConfirm(route) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure you want to delete this record?',
            text: 'If you delete this, it will be gone forever.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
            if (result.isConfirmed) {
                NProgress.start();
                let csrfToken = '{{ csrf_token() }}';
                $.ajax({
                    type: "DELETE",
                    url: route,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(resp) {
                        NProgress.done();
                        toastr.success(resp.message);
                        $('#datatable').DataTable().ajax.reload();
                    },
                    error: function(error) {
                        NProgress.done();
                        toastr.error(error.message);
                    }
                });
            }
        });
    }

    //edit
    function goToEdit(route) {
        window.location.href = route;
    }

    //view
    function goToOpen(route) {
        window.location.href = route;
    }
</script>
@endsection