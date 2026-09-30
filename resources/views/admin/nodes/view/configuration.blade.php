@extends('layouts.admin')

@section('title')
    {{ $node->name }}: @lang('admin/node_view.tabs.configuration')
@endsection

@section('content-header')
    <h1>{{ $node->name }}<small>@lang('admin/node_view.configuration.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li><a href="{{ route('admin.nodes') }}">@lang('admin/nodes.breadcrumb_nodes')</a></li>
        <li><a href="{{ route('admin.nodes.view', $node->id) }}">{{ $node->name }}</a></li>
        <li class="active">@lang('admin/node_view.tabs.configuration')</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="nav-tabs-custom nav-tabs-floating">
            <ul class="nav nav-tabs">
                <li><a href="{{ route('admin.nodes.view', $node->id) }}">@lang('admin/node_view.tabs.about')</a></li>
                <li><a href="{{ route('admin.nodes.view.settings', $node->id) }}">@lang('admin/node_view.tabs.settings')</a></li>
                <li class="active"><a href="{{ route('admin.nodes.view.configuration', $node->id) }}">@lang('admin/node_view.tabs.configuration')</a></li>
                <li><a href="{{ route('admin.nodes.view.allocation', $node->id) }}">@lang('admin/node_view.tabs.allocation')</a></li>
                <li><a href="{{ route('admin.nodes.view.servers', $node->id) }}">@lang('admin/node_view.tabs.servers')</a></li>
            </ul>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('admin/node_view.configuration.file_heading')</h3>
            </div>
            <div class="box-body">
                <pre class="no-margin">{{ $node->getYamlConfiguration() }}</pre>
            </div>
            <div class="box-footer">
                <p class="no-margin">{!! trans('admin/node_view.configuration.file_notice', ['dir' => '<code>/etc/pterodactyl</code>', 'file' => '<code>config.yml</code>']) !!}</p>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('admin/node_view.configuration.auto_deploy_heading')</h3>
            </div>
            <div class="box-body">
                <p class="text-muted small">
                    @lang('admin/node_view.configuration.auto_deploy_description')
                </p>
            </div>
            <div class="box-footer">
                <button type="button" id="configTokenBtn" class="btn btn-sm btn-default" style="width:100%;">@lang('admin/node_view.configuration.generate_token_button')</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    $('#configTokenBtn').on('click', function (event) {
        $.ajax({
            method: 'POST',
            url: '{{ route('admin.nodes.view.configuration.token', $node->id) }}',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        }).done(function (data) {
            swal({
                type: 'success',
                title: '{{ trans('admin/node_view.configuration.js.token_created_title') }}',
                text: '<p>{{ trans('admin/node_view.configuration.js.token_created_text') }}<br /><small><pre>cd /etc/pterodactyl && sudo wings configure --panel-url {{ config('app.url') }} --token ' + data.token + ' --node ' + data.node + '{{ config('app.debug') ? ' --allow-insecure' : '' }}</pre></small></p>',
                html: true
            })
        }).fail(function () {
            swal({
                title: '{{ trans('admin/node_view.configuration.js.error_title') }}',
                text: '{{ trans('admin/node_view.configuration.js.error_text') }}',
                type: 'error'
            });
        });
    });
    </script>
@endsection
