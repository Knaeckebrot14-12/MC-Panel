@extends('layouts.admin')

@section('title')
    @lang('admin/locations.title')
@endsection

@section('content-header')
    <h1>@lang('admin/locations.index.heading')<small>@lang('admin/locations.index.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/locations.breadcrumb_locations')</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('admin/locations.index.list_heading')</h3>
                <div class="box-tools">
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#newLocationModal">@lang('admin/locations.index.create_new_button')</button>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <th>@lang('admin/locations.index.table.id')</th>
                            <th>@lang('admin/locations.index.table.short_code')</th>
                            <th>@lang('admin/locations.index.table.description')</th>
                            <th class="text-center">@lang('admin/locations.index.table.nodes')</th>
                            <th class="text-center">@lang('admin/locations.index.table.servers')</th>
                        </tr>
                        @foreach ($locations as $location)
                            <tr>
                                <td><code>{{ $location->id }}</code></td>
                                <td><a href="{{ route('admin.locations.view', $location->id) }}">{{ $location->short }}</a></td>
                                <td>{{ $location->long }}</td>
                                <td class="text-center">{{ $location->nodes_count }}</td>
                                <td class="text-center">{{ $location->servers_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="newLocationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.locations') }}" method="POST">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="@lang('admin/locations.index.modal.close_aria')"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">@lang('admin/locations.index.modal.title')</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="pShortModal" class="form-label">@lang('admin/locations.index.modal.short_code_label')</label>
                            <input type="text" name="short" id="pShortModal" class="form-control" />
                            <p class="text-muted small">{!! trans('admin/locations.index.modal.short_code_description', ['example' => '<code>us.nyc.lvl3</code>']) !!}</p>
                        </div>
                        <div class="col-md-12">
                            <label for="pLongModal" class="form-label">@lang('admin/locations.index.modal.description_label')</label>
                            <textarea name="long" id="pLongModal" class="form-control" rows="4"></textarea>
                            <p class="text-muted small">@lang('admin/locations.index.modal.description_description')</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    {!! csrf_field() !!}
                    <button type="button" class="btn btn-default btn-sm pull-left" data-dismiss="modal">@lang('admin/locations.index.modal.cancel_button')</button>
                    <button type="submit" class="btn btn-success btn-sm">@lang('admin/locations.index.modal.create_button')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
