@extends('layouts.admin')

@section('title')
    @lang('admin/eggs.new.title')
@endsection

@section('content-header')
    <h1>@lang('admin/eggs.new.heading')<small>@lang('admin/eggs.new.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li><a href="{{ route('admin.nests') }}">@lang('admin/nests.breadcrumb_nests')</a></li>
        <li class="active">@lang('admin/eggs.new.breadcrumb_new')</li>
    </ol>
@endsection

@section('content')
<form action="{{ route('admin.nests.egg.new') }}" method="POST">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/eggs.new.configuration_heading')</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="pNestId" class="form-label">@lang('admin/eggs.new.nest_label')</label>
                                <div>
                                    <select name="nest_id" id="pNestId">
                                        @foreach($nests as $nest)
                                            <option value="{{ $nest->id }}" {{ old('nest_id') != $nest->id ?: 'selected' }}>{{ $nest->name }} &lt;{{ $nest->author }}&gt;</option>
                                        @endforeach
                                    </select>
                                    <p class="text-muted small">@lang('admin/eggs.new.nest_description')</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="pName" class="form-label">@lang('admin/eggs.new.name_label')</label>
                                <input type="text" id="pName" name="name" value="{{ old('name') }}" class="form-control" />
                                <p class="text-muted small">@lang('admin/eggs.new.name_description')</p>
                            </div>
                            <div class="form-group">
                                <label for="pDescription" class="form-label">@lang('admin/eggs.new.description_label')</label>
                                <textarea id="pDescription" name="description" class="form-control" rows="8">{{ old('description') }}</textarea>
                                <p class="text-muted small">@lang('admin/eggs.new.description_description')</p>
                            </div>
                            <div class="form-group">
                                <div class="checkbox checkbox-primary no-margin-bottom">
                                    <input id="pForceOutgoingIp" name="force_outgoing_ip" type="checkbox" value="1" {{ \Pterodactyl\Helpers\Utilities::checked('force_outgoing_ip', 0) }} />
                                    <label for="pForceOutgoingIp" class="strong">@lang('admin/eggs.new.force_outgoing_ip_label')</label>
                                    <p class="text-muted small">
                                        @lang('admin/eggs.new.force_outgoing_ip_description')
                                        <br>
                                        <strong>
                                            @lang('admin/eggs.new.force_outgoing_ip_warning')
                                        </strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="pDockerImage" class="control-label">@lang('admin/eggs.new.docker_images_label')</label>
                                <textarea id="pDockerImages" name="docker_images" rows="4" placeholder="ghcr.io/pterodactyl/yolks" class="form-control">{{ old('docker_images') }}</textarea>
                                <p class="text-muted small">@lang('admin/eggs.new.docker_images_description')</p>
                            </div>
                            <div class="form-group">
                                <label for="pStartup" class="control-label">@lang('admin/eggs.new.startup_label')</label>
                                <textarea id="pStartup" name="startup" class="form-control" rows="10">{{ old('startup') }}</textarea>
                                <p class="text-muted small">@lang('admin/eggs.new.startup_description')</p>
                            </div>
                            <div class="form-group">
                                <label for="pConfigFeatures" class="control-label">@lang('admin/eggs.new.features_label')</label>
                                <div>
                                    <select class="form-control" name="features[]" id="pConfigFeatures" multiple>
                                    </select>
                                    <p class="text-muted small">@lang('admin/eggs.new.features_description')</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/eggs.new.process_management_heading')</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="alert alert-warning">
                                <p>@lang('admin/eggs.new.required_notice')</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="pConfigFrom" class="form-label">@lang('admin/eggs.new.copy_settings_label')</label>
                                <select name="config_from" id="pConfigFrom" class="form-control">
                                    <option value="">@lang('admin/eggs.new.copy_settings_none')</option>
                                </select>
                                <p class="text-muted small">@lang('admin/eggs.new.copy_settings_description')</p>
                            </div>
                            <div class="form-group">
                                <label for="pConfigStop" class="form-label">@lang('admin/eggs.new.stop_command_label')</label>
                                <input type="text" id="pConfigStop" name="config_stop" class="form-control" value="{{ old('config_stop') }}" />
                                <p class="text-muted small">{!! trans('admin/eggs.new.stop_command_description', ['sigint' => '<code>SIGINT</code>', 'ctrlc' => '<code>^C</code>']) !!}</p>
                            </div>
                            <div class="form-group">
                                <label for="pConfigLogs" class="form-label">@lang('admin/eggs.new.log_config_label')</label>
                                <textarea data-action="handle-tabs" id="pConfigLogs" name="config_logs" class="form-control" rows="6">{{ old('config_logs') }}</textarea>
                                <p class="text-muted small">@lang('admin/eggs.new.log_config_description')</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="pConfigFiles" class="form-label">@lang('admin/eggs.new.config_files_label')</label>
                                <textarea data-action="handle-tabs" id="pConfigFiles" name="config_files" class="form-control" rows="6">{{ old('config_files') }}</textarea>
                                <p class="text-muted small">@lang('admin/eggs.new.config_files_description')</p>
                            </div>
                            <div class="form-group">
                                <label for="pConfigStartup" class="form-label">@lang('admin/eggs.new.start_config_label')</label>
                                <textarea data-action="handle-tabs" id="pConfigStartup" name="config_startup" class="form-control" rows="6">{{ old('config_startup') }}</textarea>
                                <p class="text-muted small">@lang('admin/eggs.new.start_config_description')</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    {!! csrf_field() !!}
                    <button type="submit" class="btn btn-success btn-sm pull-right">@lang('admin/eggs.new.create_button')</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    {!! Theme::js('vendor/lodash/lodash.js') !!}
    <script>
    $(document).ready(function() {
        $('#pNestId').select2().change();
        $('#pConfigFrom').select2();
    });
    $('#pNestId').on('change', function (event) {
        $('#pConfigFrom').html('<option value="">None</option>').select2({
            data: $.map(_.get(Pterodactyl.nests, $(this).val() + '.eggs', []), function (item) {
                return {
                    id: item.id,
                    text: item.name + ' <' + item.author + '>',
                };
            }),
        });
    });
    $('textarea[data-action="handle-tabs"]').on('keydown', function(event) {
        if (event.keyCode === 9) {
            event.preventDefault();

            var curPos = $(this)[0].selectionStart;
            var prepend = $(this).val().substr(0, curPos);
            var append = $(this).val().substr(curPos);

            $(this).val(prepend + '    ' + append);
        }
    });
    $('#pConfigFeatures').select2({
        tags: true,
        selectOnClose: false,
        tokenSeparators: [',', ' '],
    });
    </script>
@endsection
