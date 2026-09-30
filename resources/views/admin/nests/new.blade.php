@extends('layouts.admin')

@section('title')
    @lang('admin/nests.new.title')
@endsection

@section('content-header')
    <h1>@lang('admin/nests.new.heading')<small>@lang('admin/nests.new.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li><a href="{{ route('admin.nests') }}">@lang('admin/nests.breadcrumb_nests')</a></li>
        <li class="active">@lang('admin/nests.new.breadcrumb_new')</li>
    </ol>
@endsection

@section('content')
<form action="{{ route('admin.nests.new') }}" method="POST">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/nests.new.box_heading')</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label class="control-label">@lang('admin/nests.new.name_label')</label>
                        <div>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" />
                            <p class="text-muted"><small>@lang('admin/nests.new.name_description')</small></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">@lang('admin/nests.new.description_label')</label>
                        <div>
                            <textarea name="description" class="form-control" rows="6">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    {!! csrf_field() !!}
                    <button type="submit" class="btn btn-primary pull-right">@lang('admin/nests.new.save_button')</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
