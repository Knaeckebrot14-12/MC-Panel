@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'design'])

@section('title')
    @lang('admin/design.title')
@endsection

@section('content-header')
    <h1>@lang('admin/design.title')<small>@lang('admin/design.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/design.title')</li>
    </ol>
@endsection

@section('content')
    @yield('settings::nav')
    <form action="{{ route('admin.settings.design') }}" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/design.colors_heading')</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label class="control-label" for="accent">@lang('admin/design.accent_label')</label>
                            <div class="input-group" style="max-width:260px;">
                                <input id="accent-picker" type="color" value="{{ $accent }}" style="width:46px;height:34px;padding:2px;border:1px solid #444;background:transparent;" oninput="document.getElementById('accent').value = this.value">
                                <input id="accent" type="text" class="form-control" name="accent" value="{{ old('accent', $accent) }}" pattern="#[0-9a-fA-F]{6}" style="margin-left:6px;width:120px;" oninput="if (/^#[0-9a-f]{6}$/i.test(this.value)) document.getElementById('accent-picker').value = this.value">
                                <button type="button" class="btn btn-default btn-sm" style="margin-left:6px;" onclick="document.getElementById('accent').value = '{{ \Pterodactyl\Services\Branding\BrandingService::DEFAULT_ACCENT }}'; document.getElementById('accent-picker').value = '{{ \Pterodactyl\Services\Branding\BrandingService::DEFAULT_ACCENT }}';">@lang('admin/design.reset')</button>
                            </div>
                            <p class="text-muted small">@lang('admin/design.accent_description')</p>
                        </div>
                        <div class="form-group">
                            <label class="control-label">@lang('admin/design.theme_label')</label>
                            <div>
                                <label class="radio-inline"><input type="radio" name="default_theme" value="dark" @if($theme === 'dark') checked @endif> @lang('admin/design.theme_dark')</label>
                                <label class="radio-inline"><input type="radio" name="default_theme" value="light" @if($theme === 'light') checked @endif> @lang('admin/design.theme_light')</label>
                            </div>
                            <p class="text-muted small">@lang('admin/design.theme_description')</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/design.images_heading')</h3>
                    </div>
                    <div class="box-body">
                        @foreach (['logo' => $logo, 'favicon' => $favicon, 'background' => $background] as $type => $current)
                            <div class="form-group">
                                <label class="control-label" for="{{ $type }}">@lang("admin/design.{$type}_label")</label>
                                @if($current)
                                    <div style="margin-bottom:6px;">
                                        <img src="{{ $current }}" alt="" style="max-height:48px;max-width:220px;background:#222;padding:4px;border-radius:3px;">
                                        <label class="small text-muted" style="margin-left:10px;font-weight:normal;"><input type="checkbox" name="remove[]" value="{{ $type }}"> @lang('admin/design.remove')</label>
                                    </div>
                                @endif
                                <input id="{{ $type }}" type="file" name="{{ $type }}" accept="{{ $type === 'favicon' ? 'image/png,image/jpeg,image/webp,image/x-icon' : 'image/png,image/jpeg,image/webp' }}">
                                <p class="text-muted small">@lang("admin/design.{$type}_description")</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-footer">
                        {!! csrf_field() !!}
                        <span class="text-muted small">@lang('admin/design.name_hint', ['url' => route('admin.settings')])</span>
                        <button type="submit" class="btn btn-sm btn-primary pull-right">@lang('admin/design.save')</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
