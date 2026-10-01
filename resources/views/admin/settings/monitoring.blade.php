@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'monitoring'])

@section('title')
    @lang('admin/monitoring.settings.title')
@endsection

@section('content-header')
    <h1>@lang('admin/monitoring.settings.title')<small>@lang('admin/monitoring.settings.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/monitoring.settings.title')</li>
    </ol>
@endsection

@section('content')
    @yield('settings::nav')
    <div class="row">
        <div class="col-md-6">
            <form action="{{ route('admin.settings.monitoring') }}" method="POST">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/monitoring.settings.alerts_heading')</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label class="control-label" for="discord_webhook">@lang('admin/monitoring.settings.webhook_label')</label>
                            <input id="discord_webhook" type="password" autocomplete="off" class="form-control" name="discord_webhook"
                                   placeholder="{{ $hasWebhook ? trans('admin/monitoring.settings.webhook_saved') : 'https://discord.com/api/webhooks/…' }}">
                            <p class="text-muted small">@lang('admin/monitoring.settings.webhook_description')</p>
                            @if($hasWebhook)
                                <div class="checkbox checkbox-danger" style="margin-top:0;">
                                    <input id="remove_webhook" type="checkbox" name="remove_webhook" value="1">
                                    <label for="remove_webhook">@lang('admin/monitoring.settings.webhook_remove')</label>
                                </div>
                            @endif
                        </div>
                        <div class="form-group">
                            <input type="hidden" name="notify_offline" value="0">
                            <div class="checkbox checkbox-primary" style="margin-top:0;">
                                <input id="notify_offline" type="checkbox" name="notify_offline" value="1" @if(filter_var(config('mcpanel.monitoring.notify_offline'), FILTER_VALIDATE_BOOLEAN)) checked @endif>
                                <label for="notify_offline">@lang('admin/monitoring.settings.offline_label')</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-xs-6">
                                <label class="control-label" for="disk_percent">@lang('admin/monitoring.settings.disk_label')</label>
                                <div class="input-group">
                                    <input id="disk_percent" type="number" min="0" max="100" class="form-control" name="disk_percent" value="{{ old('disk_percent', (int) config('mcpanel.monitoring.disk_percent')) }}">
                                    <span class="input-group-addon">%</span>
                                </div>
                            </div>
                            <div class="form-group col-xs-6">
                                <label class="control-label" for="memory_percent">@lang('admin/monitoring.settings.memory_label')</label>
                                <div class="input-group">
                                    <input id="memory_percent" type="number" min="0" max="100" class="form-control" name="memory_percent" value="{{ old('memory_percent', (int) config('mcpanel.monitoring.memory_percent')) }}">
                                    <span class="input-group-addon">%</span>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted small">@lang('admin/monitoring.settings.threshold_description')</p>
                    </div>
                    <div class="box-footer">
                        {!! csrf_field() !!}
                        {!! method_field('PATCH') !!}
                        <button type="submit" class="btn btn-sm btn-primary pull-right">@lang('admin/monitoring.settings.save')</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/monitoring.settings.how_heading')</h3>
                </div>
                <div class="box-body">
                    <p>@lang('admin/monitoring.settings.how_text')</p>
                    <p class="text-muted small">@lang('admin/monitoring.settings.push_text')</p>
                </div>
                @if($hasWebhook)
                    <div class="box-footer">
                        <form action="{{ route('admin.settings.monitoring.test') }}" method="POST">
                            {!! csrf_field() !!}
                            <button type="submit" class="btn btn-sm btn-default"><i class="fa fa-paper-plane"></i> @lang('admin/monitoring.settings.test_button')</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
