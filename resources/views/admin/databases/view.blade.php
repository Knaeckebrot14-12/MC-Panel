@extends('layouts.admin')

@section('title')
    @lang('admin/databases.view.title', ['name' => $host->name])
@endsection

@section('content-header')
    <h1>{{ $host->name }}<small>@lang('admin/databases.view.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li><a href="{{ route('admin.databases') }}">@lang('admin/databases.breadcrumb_hosts')</a></li>
        <li class="active">{{ $host->name }}</li>
    </ol>
@endsection

@section('content')
<form action="{{ route('admin.databases.view', $host->id) }}" method="POST">
    <div class="row">
        <div class="col-sm-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/databases.view.host_details_heading')</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label for="pName" class="form-label">@lang('admin/databases.view.name_label')</label>
                        <input type="text" id="pName" name="name" class="form-control" value="{{ old('name', $host->name) }}" />
                    </div>
                    <div class="form-group">
                        <label for="pHost" class="form-label">@lang('admin/databases.view.host_label')</label>
                        <input type="text" id="pHost" name="host" class="form-control" value="{{ old('host', $host->host) }}" />
                        <p class="text-muted small">@lang('admin/databases.view.host_description')</p>
                    </div>
                    <div class="form-group">
                        <label for="pPort" class="form-label">@lang('admin/databases.view.port_label')</label>
                        <input type="text" id="pPort" name="port" class="form-control" value="{{ old('port', $host->port) }}" />
                        <p class="text-muted small">@lang('admin/databases.view.port_description')</p>
                    </div>
                    <div class="form-group">
                        <label for="pNodeId" class="form-label">@lang('admin/databases.view.linked_node_label')</label>
                        <select name="node_id" id="pNodeId" class="form-control">
                            <option value="">@lang('admin/databases.view.none')</option>
                            @foreach($locations as $location)
                                <optgroup label="{{ $location->short }}">
                                    @foreach($location->nodes as $node)
                                        <option value="{{ $node->id }}" {{ $host->node_id !== $node->id ?: 'selected' }}>{{ $node->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <p class="text-muted small">@lang('admin/databases.view.linked_node_description')</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/databases.view.user_details_heading')</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label for="pUsername" class="form-label">@lang('admin/databases.view.username_label')</label>
                        <input type="text" name="username" id="pUsername" class="form-control" value="{{ old('username', $host->username) }}" />
                        <p class="text-muted small">@lang('admin/databases.view.username_description')</p>
                    </div>
                    <div class="form-group">
                        <label for="pPassword" class="form-label">@lang('admin/databases.view.password_label')</label>
                        <input type="password" name="password" id="pPassword" class="form-control" />
                        <p class="text-muted small">@lang('admin/databases.view.password_description')</p>
                    </div>
                    <hr />
                    <p class="text-danger small text-left">
                        {!! trans('admin/databases.view.grant_notice', [
                            'must' => '<strong>' . trans('admin/databases.view.grant_notice_must') . '</strong>',
                            'grant_option' => '<code>WITH GRANT OPTION</code>',
                            'will' => '<em>' . trans('admin/databases.view.grant_notice_will') . '</em>',
                        ]) !!}
                        <strong>@lang('admin/databases.view.grant_notice_warning')</strong>
                    </p>
                </div>
                <div class="box-footer">
                    {!! csrf_field() !!}
                    <button name="_method" value="PATCH" class="btn btn-sm btn-primary pull-right">@lang('admin/databases.view.save_button')</button>
                    <button name="_method" value="DELETE" class="btn btn-sm btn-danger pull-left muted muted-hover"><i class="fa fa-trash-o"></i></button>
                </div>
            </div>
        </div>
    </div>
</form>
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('admin/databases.view.databases_heading')</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tr>
                        <th>@lang('admin/databases.view.table.server')</th>
                        <th>@lang('admin/databases.view.table.database_name')</th>
                        <th>@lang('admin/databases.view.table.username')</th>
                        <th>@lang('admin/databases.view.table.connections_from')</th>
                        <th>@lang('admin/databases.view.table.max_connections')</th>
                        <th></th>
                    </tr>
                    @foreach($databases as $database)
                        <tr>
                            <td class="middle">@if(Auth::user()->hasStaffPermission('servers.view'))<a href="{{ route('admin.servers.view', $database->getRelation('server')->id) }}">{{ $database->getRelation('server')->name }}</a>@else{{ $database->getRelation('server')->name }}@endif</td>
                            <td class="middle">{{ $database->database }}</td>
                            <td class="middle">{{ $database->username }}</td>
                            <td class="middle">{{ $database->remote }}</td>
                            @if($database->max_connections != null)
                                <td class="middle">{{ $database->max_connections }}</td>
                            @else
                                <td class="middle">@lang('admin/databases.view.table.unlimited')</td>
                            @endif
                            <td class="text-center">
                                <a href="{{ route('admin.servers.view.database', $database->getRelation('server')->id) }}">
                                    <button class="btn btn-xs btn-primary">@lang('admin/databases.view.table.manage_button')</button>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
            @if($databases->hasPages())
                <div class="box-footer with-border">
                    <div class="col-md-12 text-center">{!! $databases->render() !!}</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
        $('#pNodeId').select2();
    </script>
@endsection
