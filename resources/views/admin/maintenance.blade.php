@extends('layouts.admin')

@section('title')
    @lang('admin/maintenance.title')
@endsection

@section('content-header')
    <h1>@lang('admin/maintenance.title')<small>@lang('admin/maintenance.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/maintenance.title')</li>
    </ol>
@endsection

@section('content')
    @php
        $mode = old('mode', in_array(config('mcpanel.maintenance.mode'), ['banner', 'lock'], true) ? config('mcpanel.maintenance.mode') : 'off');
    @endphp
    <div class="row">
        <div class="col-md-8">
            <form action="{{ route('admin.maintenance') }}" method="POST">
                <div class="box {{ $mode === 'lock' ? 'box-danger' : ($mode === 'banner' ? 'box-warning' : 'box-success') }}">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/maintenance.current', ['mode' => trans('admin/maintenance.modes.' . $mode)])</h3>
                    </div>
                    <div class="box-body">
                        @foreach(['off', 'banner', 'lock'] as $option)
                            <div class="radio radio-primary">
                                <input type="radio" id="mode_{{ $option }}" name="mode" value="{{ $option }}" @if($mode === $option) checked @endif>
                                <label for="mode_{{ $option }}"><strong>@lang('admin/maintenance.modes.' . $option)</strong> — @lang('admin/maintenance.descriptions.' . $option)</label>
                            </div>
                        @endforeach
                        <div class="form-group" style="margin-top:15px;">
                            <label class="control-label" for="message">@lang('admin/maintenance.message_label')</label>
                            <textarea id="message" name="message" rows="4" class="form-control" maxlength="1000" placeholder="@lang('maintenance.default_message')">{{ old('message', config('mcpanel.maintenance.message')) }}</textarea>
                            <p class="text-muted small">@lang('admin/maintenance.message_description')</p>
                        </div>
                    </div>
                    <div class="box-footer">
                        {!! csrf_field() !!}
                        <button type="submit" name="_method" value="PATCH" class="btn btn-sm btn-primary pull-right">@lang('strings.save')</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-4">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/maintenance.info_heading')</h3>
                </div>
                <div class="box-body">
                    <ul style="padding-left:18px;margin:0;">
                        <li>@lang('admin/maintenance.info_servers')</li>
                        <li>@lang('admin/maintenance.info_staff')</li>
                        <li>@lang('admin/maintenance.info_status')</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
