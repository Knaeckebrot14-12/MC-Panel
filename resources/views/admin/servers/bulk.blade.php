@extends('layouts.admin')

@section('title')
    @lang('admin/bulk.title')
@endsection

@section('content-header')
    <h1>@lang('admin/bulk.title')<small>@lang('admin/bulk.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li><a href="{{ route('admin.servers') }}">@lang('admin/layout.nav.servers')</a></li>
        <li class="active">@lang('admin/bulk.title')</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-body">
                    <div class="form-group" style="margin-bottom:0;max-width:360px;">
                        <label class="control-label" for="bulk-node">@lang('admin/bulk.node_label')</label>
                        <select id="bulk-node" class="form-control">
                            <option value="">@lang('admin/bulk.all_nodes')</option>
                            @foreach($counts['nodes'] as $node)
                                <option value="{{ $node['id'] }}">{{ $node['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-power-off"></i> @lang('admin/bulk.power.heading')</h3>
                </div>
                <div class="box-body">
                    <p class="text-muted">@lang('admin/bulk.power.description')</p>
                    <div class="form-group">
                        <label class="control-label" for="bulk-action">@lang('admin/bulk.power.action_label')</label>
                        <select id="bulk-action" class="form-control">
                            @foreach(\Pterodactyl\Services\Servers\BulkActionService::POWER_ACTIONS as $action)
                                <option value="{{ $action }}">@lang('admin/bulk.power.actions.' . $action)</option>
                            @endforeach
                        </select>
                    </div>
                    <p><strong id="bulk-power-affected"></strong></p>
                </div>
                <div class="box-footer">
                    <button type="button" id="bulk-power-run" class="btn btn-danger pull-right">@lang('admin/bulk.power.run')</button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-commenting"></i> @lang('admin/bulk.message.heading')</h3>
                </div>
                <div class="box-body">
                    <p class="text-muted">@lang('admin/bulk.message.description')</p>
                    <div class="form-group">
                        <label class="control-label" for="bulk-message">@lang('admin/bulk.message.label')</label>
                        <input type="text" id="bulk-message" class="form-control" maxlength="{{ $messageMax }}" autocomplete="off" placeholder="@lang('admin/bulk.message.placeholder')">
                        <p class="text-muted small">@lang('admin/bulk.message.hint', ['max' => $messageMax])</p>
                    </div>
                    <p><strong id="bulk-message-affected"></strong></p>
                </div>
                <div class="box-footer">
                    <button type="button" id="bulk-message-run" class="btn btn-warning pull-right">@lang('admin/bulk.message.run')</button>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/bulk.progress.heading')</h3>
                </div>
                <div class="box-body" id="bulk-progress">
                    <p class="text-muted" id="bulk-progress-status">@lang('admin/bulk.progress.idle')</p>
                    <div id="bulk-progress-details" style="display:none;">
                        <div class="progress" style="margin-bottom:10px;">
                            <div class="progress-bar progress-bar-success" id="bulk-bar" style="width:0%;"></div>
                        </div>
                        <p>
                            <span class="label label-default"><span id="bulk-queued">0</span> @lang('admin/bulk.progress.queued')</span>
                            <span class="label label-success"><span id="bulk-ok">0</span> @lang('admin/bulk.progress.ok')</span>
                            <span class="label label-danger"><span id="bulk-failed">0</span> @lang('admin/bulk.progress.failed')</span>
                            <span class="label label-warning"><span id="bulk-skipped">0</span> @lang('admin/bulk.progress.skipped')</span>
                        </p>
                        <p class="text-muted small">@lang('admin/bulk.progress.skipped_hint')</p>
                        <div id="bulk-failures" style="display:none;">
                            <strong>@lang('admin/bulk.progress.failures')</strong>
                            <ul id="bulk-failures-list" style="margin-top:5px;"></ul>
                        </div>
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
            var T = @json(trans('admin/bulk'));
            var counts = @json($counts);
            var statusUrl = @json(route('admin.servers.bulk.status', ['run' => 'RUN']));
            var urls = {power: @json(route('admin.servers.bulk.power')), message: @json(route('admin.servers.bulk.message'))};
            var token = @json(csrf_token());
            var poller = null;

            function fmt(text, vars) {
                return String(text).replace(/:(\w+)/g, function (match, key) {
                    return Object.prototype.hasOwnProperty.call(vars, key) ? vars[key] : match;
                });
            }

            function servers(n) {
                return fmt(T.servers_count, {count: n});
            }

            function selectedNode() {
                return $('#bulk-node').val();
            }

            function nodeName() {
                return selectedNode() ? $('#bulk-node option:selected').text() : T.all_nodes;
            }

            function affected(kind) {
                var id = selectedNode();
                if (!id) {
                    return counts[kind];
                }
                var found = counts.nodes.filter(function (node) { return String(node.id) === String(id); })[0];

                return found ? found[kind] : 0;
            }

            function refresh() {
                var power = affected('power');
                var message = affected('message');
                $('#bulk-power-affected').text(fmt(T.power.affected, {servers: servers(power)}));
                $('#bulk-message-affected').text(fmt(T.message.affected, {servers: servers(message)}));
                var busy = poller !== null;
                $('#bulk-power-run').prop('disabled', busy || power === 0);
                $('#bulk-message-run').prop('disabled', busy || message === 0);
            }

            function errorText(xhr) {
                var body = xhr.responseJSON || {};
                if (body.errors) {
                    var first = Array.isArray(body.errors) ? body.errors[0] : Object.keys(body.errors).map(function (k) { return body.errors[k][0]; })[0];
                    if (first && first.detail) { return first.detail; }
                    if (typeof first === 'string') { return first; }
                }

                return body.message || T.progress.error;
            }

            function render(state) {
                $('#bulk-progress-details').show();
                var total = state.total || 0;
                var done = state.ok + state.failed + state.skipped;
                $('#bulk-bar').css('width', (total ? Math.round(done / total * 100) : 100) + '%')
                    .toggleClass('progress-bar-danger', state.failed > 0 && state.finished);
                $('#bulk-queued').text(state.queued);
                $('#bulk-ok').text(state.ok);
                $('#bulk-failed').text(state.failed);
                $('#bulk-skipped').text(state.skipped);

                var list = $('#bulk-failures-list').empty();
                (state.failures || []).forEach(function (failure) {
                    list.append($('<li>').append($('<strong>').text(failure.server || '?')).append(document.createTextNode(': ' + (failure.error || ''))));
                });
                $('#bulk-failures').toggle(list.children().length > 0);
                $('#bulk-progress-status').text(total === 0 ? T.progress.nothing : (state.finished ? T.progress.finished : T.progress.working));
            }

            function stopPolling() {
                if (poller) { clearInterval(poller); }
                poller = null;
                refresh();
            }

            function poll(run, started) {
                $.getJSON(statusUrl.replace('RUN', run)).done(function (state) {
                    render(state);
                    if (state.finished) { stopPolling(); }
                }).fail(function () {
                    $('#bulk-progress-status').text(T.progress.lost);
                    stopPolling();
                });
                if (Date.now() - started > 30 * 60 * 1000) { stopPolling(); }
            }

            function start(kind, data) {
                data._token = token;
                data.node = selectedNode();
                data.confirm = 1;
                $('#bulk-power-run, #bulk-message-run').prop('disabled', true);
                $.ajax({url: urls[kind], method: 'POST', data: data, dataType: 'json'}).done(function (response) {
                    var started = Date.now();
                    render({total: response.total, ok: 0, failed: 0, skipped: 0, queued: response.total, finished: response.total === 0, failures: []});
                    if (response.total === 0) { refresh(); return; }
                    poller = setInterval(function () { poll(response.run, started); }, 1500);
                    poll(response.run, started);
                    refresh();
                }).fail(function (xhr) {
                    $('#bulk-progress-details').hide();
                    $('#bulk-progress-status').text(xhr.status === 429 ? errorText({responseJSON: {message: xhr.statusText}}) : errorText(xhr));
                    refresh();
                });
            }

            function confirmDialog(title, text, button, onConfirm) {
                swal({
                    title: title, text: text, type: 'warning', showCancelButton: true,
                    confirmButtonColor: '#d9534f', confirmButtonText: button, closeOnConfirm: true
                }, function (isConfirm) {
                    if (isConfirm !== false) { onConfirm(); }
                });
            }

            $('#bulk-node').on('change', refresh);

            $('#bulk-power-run').on('click', function () {
                var action = $('#bulk-action').val();
                confirmDialog(T.power.confirm_title, fmt(T.power.confirm_text, {
                    action: $('#bulk-action option:selected').text(), servers: servers(affected('power')), node: nodeName()
                }), T.power.confirm_button, function () { start('power', {action: action}); });
            });

            $('#bulk-message-run').on('click', function () {
                var message = $.trim($('#bulk-message').val());
                if (message === '') {
                    $('#bulk-progress-status').text(T.message.empty);
                    return;
                }
                if (message.charAt(0) === '/') {
                    $('#bulk-progress-status').text(T.message.slash);
                    return;
                }
                confirmDialog(T.message.confirm_title, fmt(T.message.confirm_text, {
                    message: message, servers: servers(affected('message')), node: nodeName()
                }), T.message.confirm_button, function () { start('message', {message: message}); });
            });

            refresh();
        })();
    </script>
@endsection
