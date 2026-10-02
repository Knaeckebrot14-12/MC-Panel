@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'abuse'])

@section('title')
    @lang('admin/abuse.settings.title')
@endsection

@section('content-header')
    <h1>@lang('admin/abuse.settings.title')<small>@lang('admin/abuse.settings.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/abuse.settings.title')</li>
    </ol>
@endsection

@section('content')
    @yield('settings::nav')
    <form action="{{ route('admin.settings.abuse') }}" method="POST">
        <div class="row">
            <div class="col-md-6">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/abuse.settings.general_heading')</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <input type="hidden" name="enabled" value="0">
                            <div class="checkbox checkbox-primary" style="margin-top:0;">
                                <input id="enabled" type="checkbox" name="enabled" value="1" @if(old('enabled', $cfg['enabled'])) checked @endif>
                                <label for="enabled">@lang('admin/abuse.settings.enabled_label')</label>
                            </div>
                            <p class="text-muted small">@lang('admin/abuse.settings.enabled_description')</p>
                        </div>
                        <hr>
                        <h4>@lang('admin/abuse.types.cpu')</h4>
                        <div class="row">
                            <div class="form-group col-xs-6">
                                <label class="control-label" for="cpu_percent">@lang('admin/abuse.settings.cpu_percent_label')</label>
                                <div class="input-group">
                                    <input id="cpu_percent" type="number" min="50" max="100" class="form-control" name="cpu_percent" value="{{ old('cpu_percent', $cfg['cpu_percent']) }}">
                                    <span class="input-group-addon">%</span>
                                </div>
                            </div>
                            <div class="form-group col-xs-6">
                                <label class="control-label" for="cpu_minutes">@lang('admin/abuse.settings.cpu_minutes_label')</label>
                                <div class="input-group">
                                    <input id="cpu_minutes" type="number" min="10" max="1440" class="form-control" name="cpu_minutes" value="{{ old('cpu_minutes', $cfg['cpu_minutes']) }}">
                                    <span class="input-group-addon">@lang('admin/abuse.settings.minutes')</span>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted small">@lang('admin/abuse.settings.cpu_description')</p>
                        <hr>
                        <h4>@lang('admin/abuse.types.network')</h4>
                        <div class="form-group">
                            <input type="hidden" name="network_enabled" value="0">
                            <div class="checkbox checkbox-primary" style="margin-top:0;">
                                <input id="network_enabled" type="checkbox" name="network_enabled" value="1" @if(old('network_enabled', $cfg['network_enabled'])) checked @endif>
                                <label for="network_enabled">@lang('admin/abuse.settings.network_enabled_label')</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-xs-6">
                                <label class="control-label" for="network_mib_per_min">@lang('admin/abuse.settings.network_rate_label')</label>
                                <div class="input-group">
                                    <input id="network_mib_per_min" type="number" min="1" class="form-control" name="network_mib_per_min" value="{{ old('network_mib_per_min', $cfg['network_mib_per_min']) }}">
                                    <span class="input-group-addon">MiB/min</span>
                                </div>
                            </div>
                            <div class="form-group col-xs-6">
                                <label class="control-label" for="network_minutes">@lang('admin/abuse.settings.network_minutes_label')</label>
                                <div class="input-group">
                                    <input id="network_minutes" type="number" min="5" max="240" class="form-control" name="network_minutes" value="{{ old('network_minutes', $cfg['network_minutes']) }}">
                                    <span class="input-group-addon">@lang('admin/abuse.settings.minutes')</span>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted small">@lang('admin/abuse.settings.network_description')</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/abuse.types.miner')</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <input type="hidden" name="miner_enabled" value="0">
                            <div class="checkbox checkbox-primary" style="margin-top:0;">
                                <input id="miner_enabled" type="checkbox" name="miner_enabled" value="1" @if(old('miner_enabled', $cfg['miner_enabled'])) checked @endif>
                                <label for="miner_enabled">@lang('admin/abuse.settings.miner_enabled_label')</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="miner_keywords">@lang('admin/abuse.settings.keywords_label')</label>
                            <textarea id="miner_keywords" name="miner_keywords" rows="9" class="form-control" style="font-family: monospace;">{{ old('miner_keywords', $keywordsText) }}</textarea>
                            <p class="text-muted small">@lang('admin/abuse.settings.keywords_description')</p>
                        </div>
                    </div>
                </div>
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/abuse.settings.notify_heading')</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <input type="hidden" name="notify_discord" value="0">
                            <div class="checkbox checkbox-primary" style="margin-top:0;">
                                <input id="notify_discord" type="checkbox" name="notify_discord" value="1" @if(old('notify_discord', $cfg['notify_discord'])) checked @endif>
                                <label for="notify_discord">@lang('admin/abuse.settings.notify_discord_label')</label>
                            </div>
                            @unless($hasWebhook)
                                <p class="text-muted small">@lang('admin/abuse.settings.no_webhook') <a href="{{ route('admin.settings.monitoring') }}">@lang('admin/abuse.settings.to_monitoring')</a></p>
                            @endunless
                        </div>
                        <div class="form-group">
                            <input type="hidden" name="notify_push" value="0">
                            <div class="checkbox checkbox-primary" style="margin-top:0;">
                                <input id="notify_push" type="checkbox" name="notify_push" value="1" @if(old('notify_push', $cfg['notify_push'])) checked @endif>
                                <label for="notify_push">@lang('admin/abuse.settings.notify_push_label')</label>
                            </div>
                        </div>
                        <p class="text-muted small">@lang('admin/abuse.settings.notify_description')</p>
                    </div>
                    <div class="box-footer">
                        {!! csrf_field() !!}
                        {!! method_field('PATCH') !!}
                        <button type="submit" class="btn btn-sm btn-primary pull-right">@lang('admin/abuse.settings.save')</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
