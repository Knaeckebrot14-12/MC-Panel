@extends('layouts.admin')

@section('title')
    @lang('admin/servers_view.mounts.title', ['name' => $server->name])
@endsection

@section('content-header')
    <h1>{{ $server->name }}<small>@lang('admin/servers_view.mounts.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li><a href="{{ route('admin.servers') }}">@lang('admin/servers.breadcrumb_servers')</a></li>
        <li><a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a></li>
        <li class="active">@lang('admin/servers_view.mounts.breadcrumb_mounts')</li>
    </ol>
@endsection

@section('content')
    @include('admin.servers.partials.navigation')

    <div class="row">
        <div class="col-sm-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/servers_view.mounts.available_heading')</h3>
                </div>

                <div class="box-body table-responsible no-padding">
                    <table class="table table-hover">
                        <tr>
                            <th>@lang('admin/servers_view.mounts.table.id')</th>
                            <th>@lang('admin/servers_view.mounts.table.name')</th>
                            <th>@lang('admin/servers_view.mounts.table.source')</th>
                            <th>@lang('admin/servers_view.mounts.table.target')</th>
                            <th>@lang('admin/servers_view.mounts.table.status')</th>
                            <th></th>
                        </tr>

                        @foreach ($mounts as $mount)
                            <tr>
                                <td class="col-sm-1 middle"><code>{{ $mount->id }}</code></td>
                                <td class="middle">@if(Auth::user()->hasStaffPermission('mounts'))<a href="{{ route('admin.mounts.view', $mount->id) }}">{{ $mount->name }}</a>@else{{ $mount->name }}@endif</td>
                                <td class="middle"><code>{{ $mount->source }}</code></td>
                                <td class="col-sm-2 middle"><code>{{ $mount->target }}</code></td>

                                @if (! in_array($mount->id, $server->mounts->pluck('id')->toArray()))
                                    <td class="col-sm-2 middle">
                                        <span class="label label-primary">@lang('admin/servers_view.mounts.unmounted')</span>
                                    </td>

                                    <td class="col-sm-1 middle">
                                        <form action="{{ route('admin.servers.view.mounts.store', [ 'server' => $server->id ]) }}" method="POST">
                                            {!! csrf_field() !!}
                                            <input type="hidden" value="{{ $mount->id }}" name="mount_id" />
                                            <button type="submit" class="btn btn-xs btn-success"><i class="fa fa-plus"></i></button>
                                        </form>
                                    </td>
                                @else
                                    <td class="col-sm-2 middle">
                                        <span class="label label-success">@lang('admin/servers_view.mounts.mounted')</span>
                                    </td>

                                    <td class="col-sm-1 middle">
                                        <form action="{{ route('admin.servers.view.mounts.delete', [ 'server' => $server->id, 'mount' => $mount->id ]) }}" method="POST">
                                            @method('DELETE')
                                            {!! csrf_field() !!}

                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-times"></i></button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
