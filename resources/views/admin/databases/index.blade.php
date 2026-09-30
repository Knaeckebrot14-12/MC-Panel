@extends('layouts.admin')

@section('title')
    @lang('admin/databases.title')
@endsection

@section('content-header')
    <h1>@lang('admin/databases.index.heading')<small>@lang('admin/databases.index.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/databases.breadcrumb_hosts')</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('admin/databases.index.list_heading')</h3>
                <div class="box-tools">
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#newHostModal">@lang('admin/databases.index.create_new_button')</button>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <th>@lang('admin/databases.index.table.id')</th>
                            <th>@lang('admin/databases.index.table.name')</th>
                            <th>@lang('admin/databases.index.table.host')</th>
                            <th>@lang('admin/databases.index.table.port')</th>
                            <th>@lang('admin/databases.index.table.username')</th>
                            <th class="text-center">@lang('admin/databases.index.table.databases')</th>
                            <th class="text-center">@lang('admin/databases.index.table.node')</th>
                        </tr>
                        @foreach ($hosts as $host)
                            <tr>
                                <td><code>{{ $host->id }}</code></td>
                                <td><a href="{{ route('admin.databases.view', $host->id) }}">{{ $host->name }}</a></td>
                                <td><code>{{ $host->host }}</code></td>
                                <td><code>{{ $host->port }}</code></td>
                                <td>{{ $host->username }}</td>
                                <td class="text-center">{{ $host->databases_count }}</td>
                                <td class="text-center">
                                    @if(! is_null($host->node))
                                        @if(Auth::user()->isOwner())<a href="{{ route('admin.nodes.view', $host->node->id) }}">{{ $host->node->name }}</a>@else{{ $host->node->name }}@endif
                                    @else
                                        <span class="label label-default">@lang('admin/databases.index.table.none')</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="newHostModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.databases') }}" method="POST">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="@lang('admin/databases.index.modal.close_aria')"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">@lang('admin/databases.index.modal.title')</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="pName" class="form-label">@lang('admin/databases.index.modal.name_label')</label>
                        <input type="text" name="name" id="pName" class="form-control" />
                        <p class="text-muted small">{!! trans('admin/databases.index.modal.name_description', ['example' => '<code>us.nyc.lvl3</code>']) !!}</p>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="pHost" class="form-label">@lang('admin/databases.index.modal.host_label')</label>
                            <input type="text" name="host" id="pHost" class="form-control" />
                            <p class="text-muted small">@lang('admin/databases.index.modal.host_description')</p>
                        </div>
                        <div class="col-md-6">
                            <label for="pPort" class="form-label">@lang('admin/databases.index.modal.port_label')</label>
                            <input type="text" name="port" id="pPort" class="form-control" value="3306"/>
                            <p class="text-muted small">@lang('admin/databases.index.modal.port_description')</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="pUsername" class="form-label">@lang('admin/databases.index.modal.username_label')</label>
                            <input type="text" name="username" id="pUsername" class="form-control" />
                            <p class="text-muted small">@lang('admin/databases.index.modal.username_description')</p>
                        </div>
                        <div class="col-md-6">
                            <label for="pPassword" class="form-label">@lang('admin/databases.index.modal.password_label')</label>
                            <input type="password" name="password" id="pPassword" class="form-control" />
                            <p class="text-muted small">@lang('admin/databases.index.modal.password_description')</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="pNodeId" class="form-label">@lang('admin/databases.index.modal.linked_node_label')</label>
                        <select name="node_id" id="pNodeId" class="form-control">
                            <option value="">@lang('admin/databases.index.table.none')</option>
                            @foreach($locations as $location)
                                <optgroup label="{{ $location->short }}">
                                    @foreach($location->nodes as $node)
                                        <option value="{{ $node->id }}">{{ $node->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <p class="text-muted small">@lang('admin/databases.index.modal.linked_node_description')</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <p class="text-danger small text-left">
                        {!! trans('admin/databases.index.modal.grant_notice', [
                            'must' => '<strong>' . trans('admin/databases.index.modal.grant_notice_must') . '</strong>',
                            'grant_option' => '<code>WITH GRANT OPTION</code>',
                            'will' => '<em>' . trans('admin/databases.index.modal.grant_notice_will') . '</em>',
                        ]) !!}
                        <strong>@lang('admin/databases.index.modal.grant_notice_warning')</strong>
                    </p>
                    {!! csrf_field() !!}
                    <button type="button" class="btn btn-default btn-sm pull-left" data-dismiss="modal">@lang('admin/databases.index.modal.cancel_button')</button>
                    <button type="submit" class="btn btn-success btn-sm">@lang('admin/databases.index.modal.create_button')</button>
                </div>
            </form>
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
