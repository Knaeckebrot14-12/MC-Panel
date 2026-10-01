@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'subdomains'])

@section('title')
    @lang('admin/subdomains.title')
@endsection

@section('content-header')
    <h1>@lang('admin/subdomains.title')<small>@lang('admin/subdomains.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/subdomains.title')</li>
    </ol>
@endsection

@section('content')
    @yield('settings::nav')
    <div class="row">
        <div class="col-md-6">
            <form action="{{ route('admin.settings.subdomains') }}" method="POST">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/subdomains.settings_heading')</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <input type="hidden" name="enabled" value="0">
                            <div class="checkbox checkbox-primary" style="margin-top:0;">
                                <input id="enabled" type="checkbox" name="enabled" value="1" @if(filter_var(config('mcpanel.subdomains.enabled'), FILTER_VALIDATE_BOOLEAN)) checked @endif>
                                <label for="enabled">@lang('admin/subdomains.enabled_label')</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="cloudflare_token">@lang('admin/subdomains.token_label')</label>
                            <input id="cloudflare_token" type="password" autocomplete="off" class="form-control" name="cloudflare_token"
                                   placeholder="{{ $hasToken ? trans('admin/subdomains.token_saved') : '' }}">
                            <p class="text-muted small">@lang('admin/subdomains.token_description')</p>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="domains">@lang('admin/subdomains.domains_label')</label>
                            <textarea id="domains" name="domains" rows="3" class="form-control" placeholder="play.example.com">{{ old('domains', str_replace(',', "\n", (string) config('mcpanel.subdomains.domains'))) }}</textarea>
                            <p class="text-muted small">@lang('admin/subdomains.domains_description')</p>
                        </div>
                    </div>
                    <div class="box-footer">
                        {!! csrf_field() !!}
                        {!! method_field('PATCH') !!}
                        <button type="submit" class="btn btn-sm btn-primary pull-right">@lang('admin/subdomains.save')</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/subdomains.status_heading')</h3>
                </div>
                <div class="box-body">
                    @forelse($check as $domain => $ok)
                        <p>
                            <i class="fa fa-fw {{ $ok ? 'fa-check-circle text-green' : 'fa-times-circle text-red' }}"></i>
                            <code>{{ $domain }}</code> — {{ $ok ? trans('admin/subdomains.zone_ok') : trans('admin/subdomains.zone_missing') }}
                        </p>
                    @empty
                        <p class="text-muted">@lang('admin/subdomains.no_check')</p>
                    @endforelse
                    <p class="text-muted small">@lang('admin/subdomains.count', ['count' => $count])</p>
                    <hr>
                    <p class="small">@lang('admin/subdomains.how_text')</p>
                </div>
            </div>
        </div>
    </div>
@endsection
