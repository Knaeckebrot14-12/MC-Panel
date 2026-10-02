@extends('layouts.admin')

@section('title')
    {{ $node->name }}: @lang('admin/node_view.tabs.settings')
@endsection

@section('content-header')
    <h1>{{ $node->name }}<small>@lang('admin/node_view.settings.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li><a href="{{ route('admin.nodes') }}">@lang('admin/nodes.breadcrumb_nodes')</a></li>
        <li><a href="{{ route('admin.nodes.view', $node->id) }}">{{ $node->name }}</a></li>
        <li class="active">@lang('admin/node_view.tabs.settings')</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="nav-tabs-custom nav-tabs-floating">
            <ul class="nav nav-tabs">
                <li><a href="{{ route('admin.nodes.view', $node->id) }}">@lang('admin/node_view.tabs.about')</a></li>
                <li class="active"><a href="{{ route('admin.nodes.view.settings', $node->id) }}">@lang('admin/node_view.tabs.settings')</a></li>
                <li><a href="{{ route('admin.nodes.view.configuration', $node->id) }}">@lang('admin/node_view.tabs.configuration')</a></li>
                <li><a href="{{ route('admin.nodes.view.allocation', $node->id) }}">@lang('admin/node_view.tabs.allocation')</a></li>
                <li><a href="{{ route('admin.nodes.view.servers', $node->id) }}">@lang('admin/node_view.tabs.servers')</a></li>
            </ul>
        </div>
    </div>
</div>
<form action="{{ route('admin.nodes.view.settings', $node->id) }}" method="POST">
    <div class="row">
        <div class="col-sm-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/node_view.settings.settings_heading')</h3>
                </div>
                <div class="box-body row">
                    <div class="form-group col-xs-12">
                        <label for="name" class="control-label">@lang('admin/node_view.settings.name_label')</label>
                        <div>
                            <input type="text" autocomplete="off" name="name" class="form-control" value="{{ old('name', $node->name) }}" />
                            <p class="text-muted"><small>{!! trans('admin/node_view.settings.name_description', ['chars' => '<code>a-zA-Z0-9_.-</code>', 'space' => '<code>[Space]</code>']) !!}</small></p>
                        </div>
                    </div>
                    <div class="form-group col-xs-12">
                        <label for="description" class="control-label">@lang('admin/node_view.settings.description_label')</label>
                        <div>
                            <textarea name="description" id="description" rows="4" class="form-control">{{ $node->description }}</textarea>
                        </div>
                    </div>
                    <div class="form-group col-xs-12">
                        <label for="name" class="control-label">@lang('admin/node_view.settings.location_label')</label>
                        <div>
                            <select name="location_id" class="form-control">
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ (((int) old('location_id', $node->location_id)) === $location->id) ? 'selected' : '' }}>{{ $location->long }} ({{ $location->short }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-xs-12">
                        <label for="public" class="control-label">@lang('admin/node_view.settings.auto_allocation_label') <sup><a data-toggle="tooltip" data-placement="top" title="@lang('admin/node_view.settings.auto_allocation_tooltip')">?</a></sup></label>
                        <div>
                            <input type="radio" name="public" value="1" {{ (old('public', $node->public)) ? 'checked' : '' }} id="public_1" checked> <label for="public_1" style="padding-left:5px;">@lang('admin/node_view.settings.yes')</label><br />
                            <input type="radio" name="public" value="0" {{ (old('public', $node->public)) ? '' : 'checked' }} id="public_0"> <label for="public_0" style="padding-left:5px;">@lang('admin/node_view.settings.no')</label>
                        </div>
                    </div>
                    <div class="form-group col-xs-12">
                        <label for="fqdn" class="control-label">@lang('admin/node_view.settings.fqdn_label')</label>
                        <div>
                            <input type="text" autocomplete="off" name="fqdn" class="form-control" value="{{ old('fqdn', $node->fqdn) }}" />
                        </div>
                        <p class="text-muted"><small>{!! trans('admin/node_view.settings.fqdn_description', ['example' => '<code>node.example.com</code>']) !!}
                                <a tabindex="0" data-toggle="popover" data-trigger="focus" title="@lang('admin/node_view.settings.fqdn_why_title')" data-content="@lang('admin/node_view.settings.fqdn_why_content')">@lang('admin/node_view.settings.fqdn_why')</a>
                            </small></p>
                    </div>
                    <div class="form-group col-xs-12">
                        <label class="form-label"><span class="label label-warning"><i class="fa fa-power-off"></i></span> @lang('admin/node_view.settings.ssl_label')</label>
                        <div>
                            <div class="radio radio-success radio-inline">
                                <input type="radio" id="pSSLTrue" value="https" name="scheme" {{ (old('scheme', $node->scheme) === 'https') ? 'checked' : '' }}>
                                <label for="pSSLTrue"> @lang('admin/node_view.settings.ssl_option')</label>
                            </div>
                            <div class="radio radio-danger radio-inline">
                                <input type="radio" id="pSSLFalse" value="http" name="scheme" {{ (old('scheme', $node->scheme) !== 'https') ? 'checked' : '' }}>
                                <label for="pSSLFalse"> @lang('admin/node_view.settings.http_option')</label>
                            </div>
                        </div>
                        <p class="text-muted small">@lang('admin/node_view.settings.ssl_notice')</p>
                    </div>
                    <div class="form-group col-xs-12">
                        <label class="form-label"><span class="label label-warning"><i class="fa fa-power-off"></i></span> @lang('admin/node_view.settings.proxy_label')</label>
                        <div>
                            <div class="radio radio-success radio-inline">
                                <input type="radio" id="pProxyFalse" value="0" name="behind_proxy" {{ (old('behind_proxy', $node->behind_proxy) == false) ? 'checked' : '' }}>
                                <label for="pProxyFalse"> @lang('admin/node_view.settings.not_behind_proxy_option') </label>
                            </div>
                            <div class="radio radio-info radio-inline">
                                <input type="radio" id="pProxyTrue" value="1" name="behind_proxy" {{ (old('behind_proxy', $node->behind_proxy) == true) ? 'checked' : '' }}>
                                <label for="pProxyTrue"> @lang('admin/node_view.settings.behind_proxy_option') </label>
                            </div>
                        </div>
                        <p class="text-muted small">@lang('admin/node_view.settings.proxy_description')</p>
                    </div>
                    <div class="form-group col-xs-12">
                        <label class="form-label"><span class="label label-warning"><i class="fa fa-wrench"></i></span> @lang('admin/node_view.settings.maintenance_label')</label>
                        <div>
                            <div class="radio radio-success radio-inline">
                                <input type="radio" id="pMaintenanceFalse" value="0" name="maintenance_mode" {{ (old('maintenance_mode', $node->maintenance_mode) == false) ? 'checked' : '' }}>
                                <label for="pMaintenanceFalse"> @lang('admin/node_view.settings.maintenance_disabled')</label>
                            </div>
                            <div class="radio radio-warning radio-inline">
                                <input type="radio" id="pMaintenanceTrue" value="1" name="maintenance_mode" {{ (old('maintenance_mode', $node->maintenance_mode) == true) ? 'checked' : '' }}>
                                <label for="pMaintenanceTrue"> @lang('admin/node_view.settings.maintenance_enabled')</label>
                            </div>
                        </div>
                        <p class="text-muted small">@lang('admin/node_view.settings.maintenance_description')</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/node_view.settings.allocation_limits_heading')</h3>
                </div>
                <div class="box-body row">
                    <div class="col-xs-12">
                        <div class="row">
                            <div class="form-group col-xs-6">
                                <label for="memory_gb" class="control-label">@lang('admin/node_view.settings.total_memory_label')</label>
                                <div class="input-group">
                                    <input type="text" id="memory_gb" class="form-control"/>
                                    <span class="input-group-addon">GB</span>
                                </div>
                                <input type="hidden" name="memory" id="memory_mib" value="{{ old('memory', $node->memory) }}"/>
                            </div>
                            <div class="form-group col-xs-6">
                                <label for="memory_overallocate" class="control-label">@lang('admin/node_view.settings.overallocate_label')</label>
                                <div class="input-group">
                                    <input type="text" name="memory_overallocate" class="form-control" value="{{ old('memory_overallocate', $node->memory_overallocate) }}"/>
                                    <span class="input-group-addon">%</span>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted small">@lang('admin/node_view.settings.memory_notice')</p>
                    </div>
                    <div class="col-xs-12">
                        <div class="row">
                            <div class="form-group col-xs-6">
                                <label for="disk_gb" class="control-label">@lang('admin/node_view.settings.disk_space_label')</label>
                                <div class="input-group">
                                    <input type="text" id="disk_gb" class="form-control"/>
                                    <span class="input-group-addon">GB</span>
                                </div>
                                <input type="hidden" name="disk" id="disk_mib" value="{{ old('disk', $node->disk) }}"/>
                            </div>
                            <div class="form-group col-xs-6">
                                <label for="disk_overallocate" class="control-label">@lang('admin/node_view.settings.overallocate_label')</label>
                                <div class="input-group">
                                    <input type="text" name="disk_overallocate" class="form-control" value="{{ old('disk_overallocate', $node->disk_overallocate) }}"/>
                                    <span class="input-group-addon">%</span>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted small">@lang('admin/node_view.settings.disk_notice')</p>
                    </div>
                    @if($machine['memory'] > 0 && $machine['disk'] > 0)
                        <div class="col-xs-12">
                            <button type="button" id="useMachineMax" class="btn btn-sm btn-default" data-memory="{{ $machine['memory'] }}" data-disk="{{ $machine['disk'] }}">
                                @lang('admin/node_view.settings.machine_max_button')
                            </button>
                            <p class="text-muted small no-margin-bottom" style="margin-top: 6px;">
                                @lang('admin/node_view.settings.machine_max_hint', ['memory' => round($machine['memory'] / 1024, 2), 'disk' => round($machine['disk'] / 1024, 2), 'save' => trans('admin/node_view.settings.save_button')])
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/node_view.settings.general_configuration_heading')</h3>
                </div>
                <div class="box-body row">
                    <div class="form-group col-xs-12">
                        <label for="disk_overallocate" class="control-label">@lang('admin/node_view.settings.max_upload_label')</label>
                        <div class="input-group">
                            <input type="text" name="upload_size" class="form-control" value="{{ old('upload_size', $node->upload_size) }}"/>
                            <span class="input-group-addon">MiB</span>
                        </div>
                        <p class="text-muted"><small>@lang('admin/node_view.settings.max_upload_description')</small></p>
                    </div>
                    <div class="col-xs-12">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="daemonListen" class="control-label"><span class="label label-warning"><i class="fa fa-power-off"></i></span> @lang('admin/node_view.settings.daemon_port_label')</label>
                                <div>
                                    <input type="text" name="daemonListen" class="form-control" value="{{ old('daemonListen', $node->daemonListen) }}"/>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="daemonSFTP" class="control-label"><span class="label label-warning"><i class="fa fa-power-off"></i></span> @lang('admin/node_view.settings.daemon_sftp_port_label')</label>
                                <div>
                                    <input type="text" name="daemonSFTP" class="form-control" value="{{ old('daemonSFTP', $node->daemonSFTP) }}"/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <p class="text-muted"><small>{!! trans('admin/node_view.settings.sftp_notice', ['warning' => '<strong>' . trans('admin/node_view.settings.sftp_warning') . '</strong>']) !!}</small></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="maximum_servers" class="control-label">@lang('admin/node_view.settings.max_servers_label')</label>
                                <div>
                                    <input type="number" min="0" name="maximum_servers" class="form-control" value="{{ old('maximum_servers', $node->maximum_servers) }}"/>
                                </div>
                                <p class="text-muted"><small>@lang('admin/node_view.settings.max_servers_description')</small></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/node_view.settings.save_settings_heading')</h3>
                </div>
                <div class="box-body row">
                    <div class="form-group col-sm-6">
                        <div>
                            <input type="checkbox" name="reset_secret" id="reset_secret" /> <label for="reset_secret" class="control-label">@lang('admin/node_view.settings.reset_key_label')</label>
                        </div>
                        <p class="text-muted"><small>@lang('admin/node_view.settings.reset_key_description')</small></p>
                    </div>
                </div>
                <div class="box-footer">
                    {!! method_field('PATCH') !!}
                    {!! csrf_field() !!}
                    <button type="submit" class="btn btn-primary pull-right">@lang('admin/node_view.settings.save_button')</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    <script>
    $('[data-toggle="popover"]').popover({
        placement: 'auto'
    });
    $('select[name="location_id"]').select2();

    (function () {
        function bindGbField(mibId, gbId) {
            var mibInput = document.getElementById(mibId);
            var gbInput = document.getElementById(gbId);
            if (!mibInput || !gbInput) return;

            var initialMib = parseFloat(mibInput.value) || 0;
            gbInput.value = initialMib > 0 ? String(initialMib / 1024) : '';

            gbInput.addEventListener('input', function () {
                var gbValue = parseFloat(gbInput.value);
                mibInput.value = isNaN(gbValue) ? 0 : Math.round(gbValue * 1024);
            });
        }

        bindGbField('memory_mib', 'memory_gb');
        bindGbField('disk_mib', 'disk_gb');

        // Fill both limits with what the machine has; saving still needs "Save Changes".
        var machineButton = document.getElementById('useMachineMax');
        if (machineButton) {
            machineButton.addEventListener('click', function () {
                [['memory', 'memory_mib', 'memory_gb'], ['disk', 'disk_mib', 'disk_gb']].forEach(function (field) {
                    var mib = parseInt(machineButton.getAttribute('data-' + field[0]), 10) || 0;
                    document.getElementById(field[1]).value = mib;
                    document.getElementById(field[2]).value = String(Math.round(mib / 1024 * 100) / 100);
                });
            });
        }
    })();
    </script>
@endsection
