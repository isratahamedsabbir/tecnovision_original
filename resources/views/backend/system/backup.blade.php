@extends('backend.layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-8 col-xxl-8 mx-auto">
            <div class="card">
                <div class="card-header d-block d-md-flex">
                    <h3 class="h6 mb-0">{{ translate('Backup') }}</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-end gap-2">
                        <form action="{{ route('system_backup.database') }}" method="post" class="mr-2">
                            @csrf
                            <button type="submit" class="btn btn-install mt-3">
                                <i class="las la-2x la-database mr-3"></i>
                                {{ translate('Backup Database') }}
                            </button>
                        </form>
                        <form action="{{ route('system_backup.storage') }}" method="post">
                            @csrf
                            <button type="submit" class="btn btn-install mt-3">
                                <i class="las la-2x la-folder mr-3"></i>
                                {{ translate('Backup Storage') }}
                            </button>
                        </form>
                    </div>
                    <hr>
                    <div>
                        <p><i class="las la-1x la-history mr-1"></i>{{ translate('Backup History') }}</p>
                        <table class="table table-default text-center">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th>{{ translate('File Name') }}</th>
                                    <th>{{ translate('Type') }}</th>
                                    <th>{{ translate('File Size') }}</th>
                                    <th>{{ translate('Created At') }}</th>
                                    <th colspan="2">{{ translate('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($files as $key => $info)
                                    <tr>
                                        <th scope="row" class="align-middle">{{ $key + 1 }}</th>
                                        <td class="align-middle">{{ $info['name'] }}</td>
                                        <td class="align-middle">{{ translate($info['type']) }}</td>
                                        <td class="align-middle">{{ $info['size'] }}</td>
                                        <td class="align-middle">{{ $info['created_at'] }}</td>
                                        <td class="align-middle">
                                            <form action="{{ route('system_backup.destroy', $info['name']) }}" method="post">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-default btn-xs" type="submit" onclick="return confirm('{{ translate('Are you sure?') }}')" title="{{ translate('Delete File') }}"><i class="las la-2x la-trash-alt"></i></button>
                                            </form>
                                        </td>
                                        <td class="align-middle">
                                            <a href="{{ route('system_backup.download', $info['name']) }}" class="btn btn-default btn-xs" title="{{ translate('Download File') }}"><i class="las la-2x la-cloud-download-alt"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">{{ translate('No backup found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
