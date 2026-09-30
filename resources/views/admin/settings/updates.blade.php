@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'updates'])

@section('title')
    @lang('admin/update.title')
@endsection

@section('content-header')
    <h1>@lang('admin/update.heading')<small>@lang('admin/update.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/update.heading')</li>
    </ol>
@endsection

@section('content')
    @yield('settings::nav')
    <div id="update-app">
        <div class="row">
            <div class="col-xs-12">
                <div class="box" id="version-box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/update.version_heading')</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p class="text-muted small" style="margin-bottom:2px;">@lang('admin/update.installed')</p>
                                <h4 style="margin-top:0;" id="installed-version">&nbsp;</h4>
                                <p class="text-muted small" id="installed-meta"></p>
                            </div>
                            <div class="col-md-4">
                                <p class="text-muted small" style="margin-bottom:2px;">@lang('admin/update.latest')</p>
                                <h4 style="margin-top:0;" id="latest-version">&nbsp;</h4>
                                <p class="text-muted small" id="latest-meta"></p>
                            </div>
                            <div class="col-md-4">
                                <p class="text-muted small" style="margin-bottom:2px;">@lang('admin/update.status')</p>
                                <h4 style="margin-top:0;" id="state-label">&nbsp;</h4>
                                <p class="text-muted small" id="updater-state"></p>
                            </div>
                        </div>
                        <div id="latest-title" class="callout callout-info" style="display:none;margin-bottom:0;margin-top:10px;"></div>
                    </div>
                    <div class="box-footer">
                        <button class="btn btn-default btn-sm" id="btn-check"><i class="fa fa-refresh"></i> @lang('admin/update.check_button')</button>
                        <button class="btn btn-warning btn-sm pull-right" id="btn-update" disabled><i class="fa fa-arrow-circle-up"></i> @lang('admin/update.update_button')</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="progress-row" style="display:none;">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/update.progress_heading')</h3>
                    </div>
                    <div class="box-body">
                        <p id="progress-step" style="font-weight:bold;"></p>
                        <div class="progress" id="progress-bar-wrap" style="display:none;"><div class="progress-bar progress-bar-striped active" style="width:100%"></div></div>
                        <pre id="progress-log" style="max-height:260px;overflow:auto;font-size:11px;"></pre>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/update.auto_heading')</h3>
                    </div>
                    <div class="box-body">
                        <div class="checkbox checkbox-primary" style="margin-top:0;">
                            <input id="auto-update" type="checkbox">
                            <label for="auto-update">@lang('admin/update.auto_label')</label>
                        </div>
                        <p class="text-muted small" style="margin-bottom:0;">@lang('admin/update.auto_description')</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box" id="changes-box" style="display:none;">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/update.changes_heading')</h3>
                    </div>
                    <div class="box-body no-padding">
                        <table class="table table-hover" style="margin-bottom:0;"><tbody id="changes-body"></tbody></table>
                    </div>
                </div>
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/update.last_updated_heading')</h3>
                    </div>
                    <div class="box-body">
                        <h4 style="margin:0;" id="last-updated">&nbsp;</h4>
                        <p class="text-muted small" style="margin:6px 0 0;" id="last-updated-meta"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer-scripts')
    @parent
    <script>
        (function () {
            var T = {{ \Illuminate\Support\Js::from(trans('admin/update.js')) }};
            var csrf = '{{ csrf_token() }}';
            var data = {{ \Illuminate\Support\Js::from(['installed' => $installed, 'latest' => $latest, 'has_update' => $has_update, 'updater_online' => $updater_online, 'auto_update' => $auto_update, 'status' => $status, 'changes' => $changes, 'repository' => $repository, 'branch' => $branch]) }};
            var wasRunning = false;
            var offline = false;
            var poll = null;

            function ajax(method, url, body) {
                return $.ajax({
                    method: method, url: url, dataType: 'json',
                    contentType: 'application/json',
                    data: body ? JSON.stringify(body) : undefined,
                    headers: { 'X-CSRF-Token': csrf, 'Accept': 'application/json' }
                });
            }

            function fmtDate(iso) {
                if (!iso) return '';
                var d = new Date(iso);
                return isNaN(d) ? '' : d.toLocaleString();
            }

            function label(installed) {
                return installed.version + (installed.short ? ' (' + installed.short + ')' : '');
            }

            function busy(state) { return state === 'queued' || state === 'running'; }

            function render() {
                var s = data.status || { state: 'idle' };
                var box = $('#version-box').removeClass('box-success box-warning box-danger');

                $('#installed-version').text(label(data.installed));
                $('#installed-meta').text(data.installed.built_at ? T.built + ' ' + fmtDate(data.installed.built_at) : '');

                if (data.latest) {
                    $('#latest-version').text((data.latest.version ? data.latest.version + ' ' : '') + '(' + data.latest.short + ')');
                    $('#latest-meta').text(fmtDate(data.latest.date) + ' · ' + T.checked + ' ' + fmtDate(data.latest.checked_at));
                    $('#latest-title').toggle(!!data.has_update).text(data.latest.title || '');
                } else {
                    $('#latest-version').text('—');
                    $('#latest-meta').text(T.unreachable);
                    $('#latest-title').hide();
                }

                var stateText, klass = '';
                if (data.installed.is_dev) { stateText = T.state_dev; }
                else if (busy(s.state)) { stateText = T['state_' + s.state]; klass = 'box-primary'; }
                else if (data.has_update) { stateText = T.state_update; klass = 'box-warning'; }
                else if (!data.latest) { stateText = T.state_unknown; }
                else { stateText = T.state_current; klass = 'box-success'; }
                $('#state-label').text(stateText);
                box.addClass(klass);

                $('#updater-state').html(data.updater_online
                    ? '<i class="fa fa-circle text-green"></i> ' + T.updater_online
                    : '<i class="fa fa-circle text-red"></i> ' + T.updater_offline);

                var canUpdate = data.has_update && data.updater_online && !busy(s.state);
                $('#btn-update').prop('disabled', !canUpdate);
                $('#btn-check').prop('disabled', busy(s.state));
                $('#auto-update').prop('checked', !!data.auto_update);
                // When the panel was last updated: the finished update run, or else when this version was built.
                var lastUpdate = (s.state === 'success' && s.finished_at && !s.message && s.to === data.installed.short) ? s.finished_at : data.installed.built_at;
                if (lastUpdate) {
                    var minutes = Math.max(0, Math.round((Date.now() - new Date(lastUpdate).getTime()) / 60000));
                    var ago = minutes < 60 ? T.ago_minutes.replace(':count', minutes) : (minutes < 1440 ? T.ago_hours.replace(':count', Math.round(minutes / 60)) : T.ago_days.replace(':count', Math.round(minutes / 1440)));
                    $('#last-updated').text(fmtDate(lastUpdate));
                    $('#last-updated-meta').text(ago + ' · ' + T.to_version.replace(':version', label(data.installed)));
                } else {
                    $('#last-updated').text('—');
                    $('#last-updated-meta').text(T.never);
                }

                // Progress box: shown while running and after a finished run until the page is reloaded.
                var recent = s.finished_at && (Date.now() - new Date(s.finished_at).getTime()) < 30 * 60 * 1000;
                var show = busy(s.state) || (s.state && s.state !== 'idle' && recent);
                $('#progress-row').toggle(!!show);
                if (show) {
                    var step = s.step && T.steps[s.step] ? T.steps[s.step] : '';
                    var line = T['state_' + s.state] || s.state;
                    $('#progress-step').text(line + (busy(s.state) && step ? ' — ' + step : '') + (s.message ? ' — ' + s.message : ''));
                    $('#progress-bar-wrap').toggle(busy(s.state));
                    var log = $('#progress-log');
                    log.text(s.log || '');
                    log.scrollTop(log[0].scrollHeight);
                }

                var changes = data.changes || [];
                $('#changes-box').toggle(changes.length > 0);
                var body = $('#changes-body').empty();
                changes.forEach(function (c) {
                    var row = $('<tr>');
                    row.append($('<td style="width:70px;">').append($('<code>').text(c.short)));
                    row.append($('<td>').text(c.title));
                    body.append(row);
                });
            }

            function apply(payload) {
                var was = wasRunning;
                data = payload;
                var s = data.status || {};
                wasRunning = busy(s.state);
                offline = false;
                render();
                // The panel restarts itself in the middle of an update; once it is back, load the new version.
                if (was && !wasRunning && s.state === 'success') {
                    setTimeout(function () { location.reload(); }, 1500);
                }
            }

            function refresh() {
                ajax('GET', '{{ route('admin.settings.updates.status') }}').done(apply).fail(function () {
                    if (wasRunning) {
                        offline = true;
                        $('#progress-step').text(T.restarting);
                    }
                });
            }

            function errorOf(jqXHR) {
                if (jqXHR.responseJSON && jqXHR.responseJSON.error) return jqXHR.responseJSON.error;
                return jqXHR.responseText || 'Error';
            }

            $('#btn-check').on('click', function () {
                var btn = $(this).prop('disabled', true);
                ajax('POST', '{{ route('admin.settings.updates.check') }}').done(apply).fail(function (x) {
                    swal({ title: T.error_title, text: errorOf(x), type: 'error' });
                }).always(function () { btn.prop('disabled', false); });
            });

            $('#btn-update').on('click', function () {
                swal({
                    title: T.confirm_title, text: T.confirm_text, type: 'warning',
                    showCancelButton: true, confirmButtonText: T.confirm_button, cancelButtonText: T.cancel
                }, function (ok) {
                    if (!ok) return;
                    ajax('POST', '{{ route('admin.settings.updates.run') }}').done(apply).fail(function (x) {
                        swal({ title: T.error_title, text: errorOf(x), type: 'error' });
                    });
                });
            });

            $('#auto-update').on('change', function () {
                var box = $(this);
                ajax('PATCH', '{{ route('admin.settings.updates.auto') }}', { enabled: box.is(':checked') }).done(apply).fail(function (x) {
                    box.prop('checked', !box.is(':checked'));
                    swal({ title: T.error_title, text: errorOf(x), type: 'error' });
                });
            });

            wasRunning = busy((data.status || {}).state);
            render();
            poll = setInterval(refresh, 3000);
        })();
    </script>
@endsection
