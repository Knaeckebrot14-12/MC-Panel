@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'login'])

@section('title')
    @lang('admin/settings_login.title')
@endsection

@section('content-header')
    <h1>@lang('admin/settings_login.title')<small>@lang('admin/settings_login.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/settings_login.title')</li>
    </ol>
@endsection

@section('content')
    @yield('settings::nav')
    @php
        $on = fn ($key) => filter_var(config($key), FILTER_VALIDATE_BOOLEAN);
    @endphp
    <form action="{{ route('admin.settings.login') }}" method="POST">
        <div class="row">
            <div class="col-md-6">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/settings_login.registration_heading')</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <input type="hidden" name="verify_email" value="0">
                            <div class="checkbox checkbox-primary" style="margin-top:0;">
                                <input id="verify_email" type="checkbox" name="verify_email" value="1" @if(old('verify_email', $on('mcpanel.registration.verify_email'))) checked @endif>
                                <label for="verify_email">@lang('admin/settings_login.verify_email_label')</label>
                            </div>
                            <p class="text-muted small">@lang('admin/settings_login.verify_email_description')</p>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="max_accounts_per_ip">@lang('admin/settings_login.ip_limit_label')</label>
                            <input id="max_accounts_per_ip" type="number" min="0" max="1000" class="form-control" name="max_accounts_per_ip" value="{{ old('max_accounts_per_ip', (int) config('mcpanel.registration.max_accounts_per_ip')) }}">
                            <p class="text-muted small">@lang('admin/settings_login.ip_limit_description')</p>
                        </div>
                    </div>
                </div>
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/settings_login.status_heading')</h3>
                    </div>
                    <div class="box-body">
                        <input type="hidden" name="status_page" value="0">
                        <div class="checkbox checkbox-primary" style="margin-top:0;">
                            <input id="status_page" type="checkbox" name="status_page" value="1" @if(old('status_page', $on('mcpanel.status_page.enabled'))) checked @endif>
                            <label for="status_page">@lang('admin/settings_login.status_label')</label>
                        </div>
                        <p class="text-muted small">{!! trans('admin/settings_login.status_description', ['url' => '<a href="' . route('status') . '" target="_blank">' . e(route('status')) . '</a>']) !!}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-fw fa-comments"></i> @lang('admin/settings_login.discord_heading')</h3>
                    </div>
                    <div class="box-body">
                        @php $discordReady = \Pterodactyl\Http\Controllers\Base\DiscordAuthController::enabled(); @endphp
                        <p>
                            <span class="label {{ $discordReady ? 'label-success' : 'label-default' }}">{{ $discordReady ? trans('admin/settings_login.discord_status_active') : trans('admin/settings_login.discord_status_inactive') }}</span>
                        </p>
                        <div class="callout callout-info" style="margin-bottom:15px;">
                            <p style="margin-bottom:6px;"><strong>@lang('admin/settings_login.discord_steps_heading')</strong></p>
                            <ol style="padding-left:18px;margin:0;">
                                @foreach(trans('admin/settings_login.discord_steps') as $step)
                                    <li>{!! str_replace([':portal', ':redirect'], ['<a href="https://discord.com/developers/applications" target="_blank" rel="noopener">Discord Developer Portal</a>', '<code>' . e($callbackUrl) . '</code>'], e($step)) !!}</li>
                                @endforeach
                            </ol>
                        </div>
                        <div class="form-group">
                            <input type="hidden" name="discord_enabled" value="0">
                            <div class="checkbox checkbox-primary" style="margin-top:0;">
                                <input id="discord_enabled" type="checkbox" name="discord_enabled" value="1" @if(old('discord_enabled', $on('mcpanel.discord.enabled'))) checked @endif>
                                <label for="discord_enabled">@lang('admin/settings_login.discord_enabled_label')</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="discord_client_id">@lang('admin/settings_login.discord_client_id')</label>
                            <input id="discord_client_id" type="text" class="form-control" name="discord_client_id" value="{{ old('discord_client_id', config('mcpanel.discord.client_id')) }}" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="discord_client_secret">@lang('admin/settings_login.discord_client_secret')</label>
                            <input id="discord_client_secret" type="password" class="form-control" name="discord_client_secret" value="" autocomplete="new-password" placeholder="{{ $hasSecret ? trans('admin/settings_login.discord_secret_saved') : '' }}">
                            <p class="text-muted small">@lang('admin/settings_login.discord_secret_description')</p>
                        </div>
                        <div class="form-group">
                            <label class="control-label">@lang('admin/settings_login.discord_redirect')</label>
                            <input type="text" class="form-control" value="{{ $callbackUrl }}" readonly onclick="this.select()">
                            <p class="text-muted small">@lang('admin/settings_login.discord_redirect_description')</p>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <input type="hidden" name="discord_allow_registration" value="0">
                            <div class="checkbox checkbox-primary" style="margin-top:0;">
                                <input id="discord_allow_registration" type="checkbox" name="discord_allow_registration" value="1" @if(old('discord_allow_registration', $on('mcpanel.discord.allow_registration'))) checked @endif>
                                <label for="discord_allow_registration">@lang('admin/settings_login.discord_registration_label')</label>
                            </div>
                            <p class="text-muted small">@lang('admin/settings_login.discord_registration_description')</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-footer">
                        {!! csrf_field() !!}
                        <button type="submit" name="_method" value="PATCH" class="btn btn-sm btn-primary pull-right">@lang('strings.save')</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
