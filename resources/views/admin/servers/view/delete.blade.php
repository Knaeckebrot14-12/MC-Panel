@extends('layouts.admin')

@section('title')
    @lang('admin/servers_view.delete.title', ['name' => $server->name])
@endsection

@section('content-header')
    <h1>{{ $server->name }}<small>@lang('admin/servers_view.delete.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li><a href="{{ route('admin.servers') }}">@lang('admin/servers.breadcrumb_servers')</a></li>
        <li><a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a></li>
        <li class="active">@lang('admin/servers_view.delete.breadcrumb_delete')</li>
    </ol>
@endsection

@section('content')
@include('admin.servers.partials.navigation')
<div class="row">
    <div class="col-md-6">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('admin/servers_view.delete.safe_heading')</h3>
            </div>
            <div class="box-body">
                <p>@lang('admin/servers_view.delete.safe_description')</p>
                <p class="text-danger small">{!! trans('admin/servers_view.delete.irreversible_notice', ['all_data' => '<strong>' . trans('admin/servers_view.delete.all_server_data') . '</strong>']) !!}</p>
            </div>
            <div class="box-footer">
                <form id="deleteform" action="{{ route('admin.servers.view.delete', $server->id) }}" method="POST">
                    {!! csrf_field() !!}
                    <button id="deletebtn" class="btn btn-danger">@lang('admin/servers_view.delete.safe_button')</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('admin/servers_view.delete.force_heading')</h3>
            </div>
            <div class="box-body">
                <p>@lang('admin/servers_view.delete.force_description')</p>
                <p class="text-danger small">{!! trans('admin/servers_view.delete.force_irreversible_notice', ['all_data' => '<strong>' . trans('admin/servers_view.delete.all_server_data') . '</strong>']) !!}</p>
            </div>
            <div class="box-footer">
                <form id="forcedeleteform" action="{{ route('admin.servers.view.delete', $server->id) }}" method="POST">
                    {!! csrf_field() !!}
                    <input type="hidden" name="force_delete" value="1" />
                    <button id="forcedeletebtn"" class="btn btn-danger">@lang('admin/servers_view.delete.force_button')</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    $('#deletebtn').click(function (event) {
        event.preventDefault();
        swal({
            title: '',
            type: 'warning',
            text: '{{ trans('admin/servers_view.delete.js.confirm_text') }}',
            showCancelButton: true,
            confirmButtonText: '{{ trans('admin/servers_view.delete.js.confirm_button') }}',
            confirmButtonColor: '#d9534f',
            closeOnConfirm: false
        }, function () {
            $('#deleteform').submit()
        });
    });

    $('#forcedeletebtn').click(function (event) {
        event.preventDefault();
        swal({
            title: '',
            type: 'warning',
            text: '{{ trans('admin/servers_view.delete.js.confirm_text') }}',
            showCancelButton: true,
            confirmButtonText: '{{ trans('admin/servers_view.delete.js.confirm_button') }}',
            confirmButtonColor: '#d9534f',
            closeOnConfirm: false
        }, function () {
            $('#forcedeleteform').submit()
        });
    });
    </script>
@endsection
