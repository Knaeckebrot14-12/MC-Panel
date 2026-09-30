@extends('layouts.admin')

@section('title')
    @lang('admin/nodes.breadcrumb_nodes') &rarr; @lang('admin/nodes.new.breadcrumb_new')
@endsection

@section('content-header')
    <h1>@lang('admin/nodes.new.heading')<small>@lang('admin/nodes.new.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li><a href="{{ route('admin.nodes') }}">@lang('admin/nodes.breadcrumb_nodes')</a></li>
        <li class="active">@lang('admin/nodes.new.breadcrumb_new')</li>
    </ol>
@endsection

@section('content')
<form action="{{ route('admin.nodes.new') }}" method="POST">
    <div class="row">
        <div class="col-sm-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/nodes.new.basic_details_heading')</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label for="pName" class="form-label">@lang('admin/nodes.new.name_label')</label>
                        <input type="text" name="name" id="pName" class="form-control" value="{{ old('name') }}"/>
                        <p class="text-muted small">{!! trans('admin/nodes.new.name_description', ['chars' => '<code>a-zA-Z0-9_.-</code>', 'space' => '<code>[Space]</code>']) !!}</p>
                    </div>
                    <div class="form-group">
                        <label for="pDescription" class="form-label">@lang('admin/nodes.new.description_label')</label>
                        <textarea name="description" id="pDescription" rows="4" class="form-control">{{ old('description') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="pLocationId" class="form-label">@lang('admin/nodes.new.location_label')</label>
                        <select name="location_id" id="pLocationId">
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" {{ $location->id != old('location_id') ?: 'selected' }}>{{ $location->short }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">@lang('admin/nodes.new.visibility_label')</label>
                        <div>
                            <div class="radio radio-success radio-inline">

                                <input type="radio" id="pPublicTrue" value="1" name="public" checked>
                                <label for="pPublicTrue"> @lang('admin/nodes.new.public_option') </label>
                            </div>
                            <div class="radio radio-danger radio-inline">
                                <input type="radio" id="pPublicFalse" value="0" name="public">
                                <label for="pPublicFalse"> @lang('admin/nodes.new.private_option') </label>
                            </div>
                        </div>
                        <p class="text-muted small">{!! trans('admin/nodes.new.visibility_description', ['private' => '<code>' . trans('admin/nodes.new.private_option') . '</code>']) !!}
                    </div>
                    <div class="form-group">
                        <label for="pFQDN" class="form-label">@lang('admin/nodes.new.fqdn_label')</label>
                        <input type="text" name="fqdn" id="pFQDN" class="form-control" value="{{ old('fqdn') }}"/>
                        <p class="text-muted small">{!! trans('admin/nodes.new.fqdn_description', ['example' => '<code>node.example.com</code>', 'only' => '<em>' . trans('admin/nodes.new.fqdn_only') . '</em>']) !!}</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">@lang('admin/nodes.new.ssl_label')</label>
                        <div>
                            <div class="radio radio-success radio-inline">
                                <input type="radio" id="pSSLTrue" value="https" name="scheme" checked>
                                <label for="pSSLTrue"> @lang('admin/nodes.new.ssl_option')</label>
                            </div>
                            <div class="radio radio-danger radio-inline">
                                <input type="radio" id="pSSLFalse" value="http" name="scheme" @if(request()->isSecure()) disabled @endif>
                                <label for="pSSLFalse"> @lang('admin/nodes.new.http_option')</label>
                            </div>
                        </div>
                        @if(request()->isSecure())
                            <p class="text-danger small">{!! trans('admin/nodes.new.ssl_forced_notice', ['must' => '<strong>' . trans('admin/nodes.new.ssl_forced_must') . '</strong>']) !!}</p>
                        @else
                            <p class="text-muted small">@lang('admin/nodes.new.ssl_notice')</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="form-label">@lang('admin/nodes.new.proxy_label')</label>
                        <div>
                            <div class="radio radio-success radio-inline">
                                <input type="radio" id="pProxyFalse" value="0" name="behind_proxy" checked>
                                <label for="pProxyFalse"> @lang('admin/nodes.new.not_behind_proxy_option') </label>
                            </div>
                            <div class="radio radio-info radio-inline">
                                <input type="radio" id="pProxyTrue" value="1" name="behind_proxy">
                                <label for="pProxyTrue"> @lang('admin/nodes.new.behind_proxy_option') </label>
                            </div>
                        </div>
                        <p class="text-muted small">@lang('admin/nodes.new.proxy_description')</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/nodes.new.configuration_heading')</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="pDaemonBase" class="form-label">@lang('admin/nodes.new.daemon_base_label')</label>
                            <input type="text" name="daemonBase" id="pDaemonBase" class="form-control" value="/var/lib/pterodactyl/volumes" />
                            <p class="text-muted small">{!! trans('admin/nodes.new.daemon_base_description', [
                                'ovh_notice' => '<strong>' . trans('admin/nodes.new.daemon_base_ovh_notice', ['path' => '<code>/home/daemon-data</code>']) . '</strong>',
                            ]) !!}</p>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pMemory" class="form-label">@lang('admin/nodes.new.total_memory_label')</label>
                            <div class="input-group">
                                <input type="text" name="memory" data-multiplicator="true" class="form-control" id="pMemory" value="{{ old('memory') }}"/>
                                <span class="input-group-addon">MiB</span>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pMemoryOverallocate" class="form-label">@lang('admin/nodes.new.memory_overallocate_label')</label>
                            <div class="input-group">
                                <input type="text" name="memory_overallocate" class="form-control" id="pMemoryOverallocate" value="{{ old('memory_overallocate') }}"/>
                                <span class="input-group-addon">%</span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <p class="text-muted small">{!! trans('admin/nodes.new.memory_notice', ['neg_one' => '<code>-1</code>', 'zero' => '<code>0</code>']) !!}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="pDisk" class="form-label">@lang('admin/nodes.new.total_disk_label')</label>
                            <div class="input-group">
                                <input type="text" name="disk" data-multiplicator="true" class="form-control" id="pDisk" value="{{ old('disk') }}"/>
                                <span class="input-group-addon">MiB</span>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pDiskOverallocate" class="form-label">@lang('admin/nodes.new.disk_overallocate_label')</label>
                            <div class="input-group">
                                <input type="text" name="disk_overallocate" class="form-control" id="pDiskOverallocate" value="{{ old('disk_overallocate') }}"/>
                                <span class="input-group-addon">%</span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <p class="text-muted small">{!! trans('admin/nodes.new.disk_notice', ['neg_one' => '<code>-1</code>', 'zero' => '<code>0</code>']) !!}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="pDaemonListen" class="form-label">@lang('admin/nodes.new.daemon_port_label')</label>
                            <input type="text" name="daemonListen" class="form-control" id="pDaemonListen" value="8080" />
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pDaemonSFTP" class="form-label">@lang('admin/nodes.new.daemon_sftp_port_label')</label>
                            <input type="text" name="daemonSFTP" class="form-control" id="pDaemonSFTP" value="2022" />
                        </div>
                        <div class="col-md-12">
                            <p class="text-muted small">{!! trans('admin/nodes.new.sftp_notice', [
                                'warning' => '<strong>' . trans('admin/nodes.new.sftp_warning') . '</strong>',
                                'port' => '<code>8443</code>',
                            ]) !!}</p>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    {!! csrf_field() !!}
                    <button type="submit" class="btn btn-success pull-right">@lang('admin/nodes.new.create_button')</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    <script>
        $('#pLocationId').select2();
    </script>
@endsection
