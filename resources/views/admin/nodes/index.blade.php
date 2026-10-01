@extends('layouts.admin')

@section('title')
    @lang('admin/nodes.title')
@endsection

@section('scripts')
    @parent
    {!! Theme::css('vendor/fontawesome/animation.min.css') !!}
@endsection

@section('content-header')
    <h1>@lang('admin/nodes.index.heading')<small>@lang('admin/nodes.index.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/nodes.breadcrumb_nodes')</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('admin/nodes.index.list_heading')</h3>
                <div class="box-tools search01">
                    <form action="{{ route('admin.nodes') }}" method="GET">
                        <div class="input-group input-group-sm">
                            <input type="text" name="filter[name]" class="form-control pull-right" value="{{ request()->input('filter.name') }}" placeholder="@lang('admin/nodes.index.search_placeholder')">
                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                                <a href="{{ route('admin.nodes.new') }}"><button type="button" class="btn btn-sm btn-primary" style="border-radius: 0 3px 3px 0;margin-left:-1px;">@lang('admin/nodes.index.create_new_button')</button></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="box-body" id="wings-update-all" style="display:none;padding-bottom:0;">
                <form action="{{ route('admin.nodes.wings-update') }}" method="POST" class="alert alert-warning" style="margin-bottom:10px;" onsubmit="this.querySelector('button').disabled = true;">
                    {!! csrf_field() !!}
                    <span id="wings-update-all-text"></span>
                    <button type="submit" class="btn btn-xs btn-default pull-right"><i class="fa fa-cloud-download"></i> @lang('admin/monitoring.wings.update_all_button')</button>
                </form>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <th></th>
                            <th>@lang('admin/nodes.index.table.name')</th>
                            <th>@lang('admin/nodes.index.table.location')</th>
                            <th>@lang('admin/nodes.index.table.memory')</th>
                            <th>@lang('admin/nodes.index.table.disk')</th>
                            <th class="text-center">@lang('admin/nodes.index.table.servers')</th>
                            <th class="text-center">@lang('admin/monitoring.node.usage_column')</th>
                            <th class="text-center">@lang('admin/nodes.index.table.ssl')</th>
                            <th class="text-center">@lang('admin/nodes.index.table.public')</th>
                        </tr>
                        @foreach ($nodes as $node)
                            <tr>
                                <td class="text-center text-muted left-icon" data-node="{{ $node->id }}"><i class="fa fa-fw fa-refresh fa-spin"></i></td>
                                <td>{!! $node->maintenance_mode ? '<span class="label label-warning"><i class="fa fa-wrench"></i></span> ' : '' !!}<a href="{{ route('admin.nodes.view', $node->id) }}">{{ $node->name }}</a></td>
                                <td>{{ $node->location->short }}</td>
                                <td>{{ $node->memory }} MiB</td>
                                <td>{{ $node->disk }} MiB</td>
                                <td class="text-center">
                                    @if (is_null($node->maximum_servers))
                                        {{ $node->servers_count }}
                                    @else
                                        <span class="{{ $node->servers_count > $node->maximum_servers ? 'text-red' : '' }}">{{ $node->servers_count }}/{{ $node->maximum_servers }}</span>
                                    @endif
                                </td>
                                <td class="text-center small" data-usage="{{ $node->id }}">–</td>
                                <td class="text-center" style="color:{{ ($node->scheme === 'https') ? '#50af51' : '#d9534f' }}"><i class="fa fa-{{ ($node->scheme === 'https') ? 'lock' : 'unlock' }}"></i></td>
                                <td class="text-center"><i class="fa fa-{{ ($node->public) ? 'eye' : 'eye-slash' }}"></i></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($nodes->hasPages())
                <div class="box-footer with-border">
                    <div class="col-md-12 text-center">{!! $nodes->appends(['query' => Request::input('query')])->render() !!}</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    // Node status comes from the panel's own monitoring (Wings is checked every minute on the
    // server side), so the page no longer needs each node's secret token.
    (function refreshNodes() {
        var T = @json(trans('admin/monitoring.node.js'));
        $.get('{{ route('admin.nodes.monitoring') }}').done(function (data) {
            var outdated = 0;
            data.nodes.forEach(function (node) {
                var cell = $('td[data-node="' + node.id + '"]');
                if (!cell.length) return;
                var icon = cell.removeClass('text-muted').find('i').tooltip('destroy');
                var version = node.version ? 'v' + String(node.version).replace(/^v/i, '') : '';
                if (node.online) {
                    icon.removeClass().addClass('fa fa-fw fa-heartbeat faa-pulse animated').css('color', node.outdated ? '#f39c12' : '#50af51');
                } else {
                    icon.removeClass().addClass('fa fa-fw fa-heart-o').css('color', '#d9534f');
                }
                icon.tooltip({ title: (node.online ? version : T.offline) + (node.outdated ? ' — ' + T.update_short.replace(':latest', data.latest) : '') });
                if (node.outdated && node.capable) outdated++;

                var usage = [];
                if (node.cpu !== null) usage.push('CPU ' + Math.round(node.cpu) + '%');
                if (node.memory !== null) usage.push('RAM ' + node.memory + '%');
                if (node.disk !== null) usage.push(T.disk + ' ' + node.disk + '%');
                $('td[data-usage="' + node.id + '"]').text(node.online && usage.length ? usage.join(' · ') : '–');
            });
            if (outdated > 0) {
                $('#wings-update-all-text').text(T.update_all.replace(':count', outdated).replace(':latest', data.latest));
                $('#wings-update-all').show();
            }
        }).always(function () {
            setTimeout(refreshNodes, 30000);
        });
    })();
    </script>
@endsection
