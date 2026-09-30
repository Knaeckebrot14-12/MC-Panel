
@extends('layouts.admin')

@section('title')
    @lang('admin/mounts.title')
@endsection

@section('content-header')
    <h1>@lang('admin/mounts.index.heading')<small>@lang('admin/mounts.index.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/mounts.breadcrumb_mounts')</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/mounts.index.list_heading')</h3>

                    <div class="box-tools">
                        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#newMountModal">@lang('admin/mounts.index.create_new_button')</button>
                    </div>
                </div>

                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <tbody>
                            <tr>
                                <th>@lang('admin/mounts.index.table.id')</th>
                                <th>@lang('admin/mounts.index.table.name')</th>
                                <th>@lang('admin/mounts.index.table.source')</th>
                                <th>@lang('admin/mounts.index.table.target')</th>
                                <th class="text-center">@lang('admin/mounts.index.table.eggs')</th>
                                <th class="text-center">@lang('admin/mounts.index.table.nodes')</th>
                                <th class="text-center">@lang('admin/mounts.index.table.servers')</th>
                            </tr>

                            @foreach ($mounts as $mount)
                                <tr>
                                    <td><code>{{ $mount->id }}</code></td>
                                    <td><a href="{{ route('admin.mounts.view', $mount->id) }}">{{ $mount->name }}</a></td>
                                    <td><code>{{ $mount->source }}</code></td>
                                    <td><code>{{ $mount->target }}</code></td>
                                    <td class="text-center">{{ $mount->eggs_count }}</td>
                                    <td class="text-center">{{ $mount->nodes_count }}</td>
                                    <td class="text-center">{{ $mount->servers_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="newMountModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.mounts') }}" method="POST">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="@lang('admin/mounts.index.modal.close_aria')">
                            <span aria-hidden="true" style="color: #FFFFFF">&times;</span>
                        </button>

                        <h4 class="modal-title">@lang('admin/mounts.index.modal.title')</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="pName" class="form-label">@lang('admin/mounts.index.modal.name_label')</label>
                                <input type="text" id="pName" name="name" class="form-control" />
                                <p class="text-muted small">@lang('admin/mounts.index.modal.name_description')</p>
                            </div>

                            <div class="col-md-12">
                                <label for="pDescription" class="form-label">@lang('admin/mounts.index.modal.description_label')</label>
                                <textarea id="pDescription" name="description" class="form-control" rows="4"></textarea>
                                <p class="text-muted small">@lang('admin/mounts.index.modal.description_description')</p>
                            </div>

                            <div class="col-md-6">
                                <label for="pSource" class="form-label">@lang('admin/mounts.index.modal.source_label')</label>
                                <input type="text" id="pSource" name="source" class="form-control" />
                                <p class="text-muted small">@lang('admin/mounts.index.modal.source_description')</p>
                            </div>

                            <div class="col-md-6">
                                <label for="pTarget" class="form-label">@lang('admin/mounts.index.modal.target_label')</label>
                                <input type="text" id="pTarget" name="target" class="form-control" />
                                <p class="text-muted small">@lang('admin/mounts.index.modal.target_description')</p>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">@lang('admin/mounts.index.modal.read_only_label')</label>

                                <div>
                                    <div class="radio radio-success radio-inline">
                                        <input type="radio" id="pReadOnlyFalse" name="read_only" value="0" checked>
                                        <label for="pReadOnlyFalse">@lang('admin/mounts.index.modal.false')</label>
                                    </div>

                                    <div class="radio radio-warning radio-inline">
                                        <input type="radio" id="pReadOnly" name="read_only" value="1">
                                        <label for="pReadOnly">@lang('admin/mounts.index.modal.true')</label>
                                    </div>
                                </div>

                                <p class="text-muted small">@lang('admin/mounts.index.modal.read_only_description')</p>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">@lang('admin/mounts.index.modal.user_mountable_label')</label>

                                <div>
                                    <div class="radio radio-success radio-inline">
                                        <input type="radio" id="pUserMountableFalse" name="user_mountable" value="0" checked>
                                        <label for="pUserMountableFalse">@lang('admin/mounts.index.modal.false')</label>
                                    </div>

                                    <div class="radio radio-warning radio-inline">
                                        <input type="radio" id="pUserMountable" name="user_mountable" value="1">
                                        <label for="pUserMountable">@lang('admin/mounts.index.modal.true')</label>
                                    </div>
                                </div>

                                <p class="text-muted small">@lang('admin/mounts.index.modal.user_mountable_description')</p>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        {!! csrf_field() !!}
                        <button type="button" class="btn btn-default btn-sm pull-left" data-dismiss="modal">@lang('admin/mounts.index.modal.cancel_button')</button>
                        <button type="submit" class="btn btn-success btn-sm">@lang('admin/mounts.index.modal.create_button')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
