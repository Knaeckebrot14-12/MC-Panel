@extends('layouts.admin')

@section('title')
    @lang('admin/index.title')
@endsection

@section('content-header')
    <h1>@lang('admin/index.heading')<small>@lang('admin/index.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/index.breadcrumb_index')</li>
    </ol>
@endsection

@section('content')
@if(!Auth::user()->use_totp)
    <div class="row">
        <div class="col-xs-12">
            <div class="alert alert-danger">
                <i class="fa fa-shield"></i> @lang('admin/index.security.no_2fa')
                <a href="/account" class="btn btn-xs btn-default pull-right">@lang('admin/index.security.enable_2fa')</a>
            </div>
        </div>
    </div>
@endif
<div class="row">
    <div class="col-xs-12">
        @php
            $updateInfo = app(\Pterodactyl\Services\Update\UpdateService::class)->summary();
            $installedLabel = $updateInfo['installed']['version'] . ($updateInfo['installed']['short'] ? ' (' . $updateInfo['installed']['short'] . ')' : '');
        @endphp
        <div class="box {{ $updateInfo['has_update'] ? 'box-warning' : 'box-success' }}">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('admin/index.system_info_heading')</h3>
            </div>
            <div class="box-body">
                @if ($updateInfo['installed']['is_dev'])
                    @lang('admin/update.index.dev_build')
                @elseif ($updateInfo['has_update'])
                    {!! trans('admin/update.index.available', ['installed' => '<code>' . e($installedLabel) . '</code>', 'latest' => '<code>' . e(($updateInfo['latest']['version'] ?? '?') . ' (' . $updateInfo['latest']['short'] . ')') . '</code>']) !!}
                    @if(Auth::user()->effectiveRole() === 'owner')
                        <a href="{{ route('admin.settings.updates') }}" class="btn btn-warning btn-xs" style="margin-left:8px;">@lang('admin/update.index.open')</a>
                    @endif
                @else
                    {!! trans('admin/update.index.up_to_date', ['version' => '<code>' . e($installedLabel) . '</code>']) !!}
                @endif
                @php
                    $ssl = \Illuminate\Support\Facades\Cache::get(\Pterodactyl\Console\Commands\Maintenance\RenewCertificateCommand::CACHE_KEY);
                    $sslExpires = !empty($ssl['expires_at']) ? \Carbon\Carbon::parse($ssl['expires_at']) : null;
                @endphp
                @if(!empty($ssl['managed']) && $sslExpires)
                    <p style="margin:8px 0 0;">
                        <i class="fa fa-lock {{ !empty($ssl['error']) || $sslExpires->lt(now()->addDays(14)) ? 'text-yellow' : 'text-green' }}"></i>
                        @lang('admin/index.ssl.valid_until', ['domain' => $ssl['domain'], 'date' => $sslExpires->format('d.m.Y'), 'days' => max(0, (int) now()->diffInDays($sslExpires))])
                        @if(!empty($ssl['error']))
                            <br><span class="text-yellow">@lang('admin/index.ssl.renew_failed')</span>
                        @endif
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="row">
    @php
        $cards = [
            ['icon' => 'fa-users', 'color' => 'bg-aqua', 'label' => trans('admin/index.stats.users'), 'value' => $stats['users'], 'sub' => trans('admin/index.stats.users_sub', ['new' => $stats['users_new_7d'], 'unverified' => $stats['users_unverified']]), 'link' => route('admin.users')],
            ['icon' => 'fa-server', 'color' => 'bg-green', 'label' => trans('admin/index.stats.servers'), 'value' => $stats['servers'], 'sub' => trans('admin/index.stats.servers_sub', ['suspended' => $stats['servers_suspended'], 'coins' => $stats['servers_coin_funded']]), 'link' => route('admin.servers')],
            ['icon' => 'fa-life-ring', 'color' => 'bg-yellow', 'label' => trans('admin/index.stats.tickets'), 'value' => $stats['tickets_open'], 'sub' => trans('admin/index.stats.tickets_sub'), 'link' => route('admin.tickets')],
            ['icon' => 'fa-database', 'color' => 'bg-purple', 'label' => trans('admin/index.stats.coins'), 'value' => number_format($stats['coins_circulating'], 0, ',', '.'), 'sub' => trans('admin/index.stats.coins_sub', ['earned' => number_format($stats['coins_earned_30d'], 0, ',', '.'), 'spent' => number_format($stats['coins_spent_30d'], 0, ',', '.')]), 'link' => null],
        ];
    @endphp
    @foreach($cards as $card)
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon {{ $card['color'] }}"><i class="fa {{ $card['icon'] }}"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@if($card['link'])<a href="{{ $card['link'] }}" style="color:inherit;">{{ $card['label'] }}</a>@else{{ $card['label'] }}@endif</span>
                    <span class="info-box-number">{{ $card['value'] }}</span>
                    <span class="text-muted small">{{ $card['sub'] }}</span>
                </div>
            </div>
        </div>
    @endforeach
</div>
<div class="row">
    <div class="col-md-6">
        <div class="box">
            <div class="box-header with-border"><h3 class="box-title">@lang('admin/index.stats.registrations_chart')</h3></div>
            <div class="box-body"><canvas id="chart-registrations" height="140"></canvas></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box">
            <div class="box-header with-border"><h3 class="box-title">@lang('admin/index.stats.coins_chart')</h3></div>
            <div class="box-body"><canvas id="chart-coins" height="140"></canvas></div>
        </div>
    </div>
</div>
@if(count($stats['nodes']))
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border"><h3 class="box-title">@lang('admin/index.stats.nodes_heading')</h3></div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tr>
                        <th>@lang('admin/index.stats.node')</th>
                        <th class="text-center">@lang('admin/index.stats.servers')</th>
                        <th style="width:30%;">@lang('admin/index.stats.memory')</th>
                        <th style="width:30%;">@lang('admin/index.stats.disk')</th>
                    </tr>
                    @foreach($stats['nodes'] as $node)
                        <tr>
                            <td>
                                @if(Auth::user()->isOwner())<a href="{{ route('admin.nodes.view', $node['id']) }}">{{ $node['name'] }}</a>@else{{ $node['name'] }}@endif
                                @if($node['maintenance'])<span class="label label-warning">@lang('admin/index.stats.node_maintenance')</span>@endif
                            </td>
                            <td class="text-center">{{ $node['servers'] }}</td>
                            @foreach(['memory_percent', 'disk_percent'] as $key)
                                <td>
                                    <div class="progress progress-xs" style="margin:6px 0 0;">
                                        <div class="progress-bar {{ $node[$key] >= 90 ? 'progress-bar-danger' : ($node[$key] >= 70 ? 'progress-bar-warning' : 'progress-bar-success') }}" style="width: {{ min(100, $node[$key]) }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $node[$key] }}%</small>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>
@endif
<div class="row">
    <div class="col-xs-6 col-sm-3 text-center">
        <a href="{{ $version->getDiscord() }}"><button class="btn btn-warning" style="width:100%;"><i class="fa fa-fw fa-support"></i> @lang('admin/index.get_help') <small>@lang('admin/index.via_discord')</small></button></a>
    </div>
    <div class="col-xs-6 col-sm-3 text-center">
        <a href="https://pterodactyl.io"><button class="btn btn-primary" style="width:100%;"><i class="fa fa-fw fa-link"></i> @lang('admin/index.documentation')</button></a>
    </div>
    <div class="clearfix visible-xs-block">&nbsp;</div>
    <div class="col-xs-6 col-sm-3 text-center">
        <a href="https://github.com/pterodactyl/panel"><button class="btn btn-primary" style="width:100%;"><i class="fa fa-fw fa-support"></i> @lang('admin/index.github')</button></a>
    </div>
    <div class="col-xs-6 col-sm-3 text-center">
        <a href="{{ $version->getDonations() }}"><button class="btn btn-success" style="width:100%;"><i class="fa fa-fw fa-money"></i> @lang('admin/index.support_project')</button></a>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <style>
        /* Match the stat tiles to the dark admin boxes. */
        .info-box { background: #3f4d5a; color: #cad1d8; box-shadow: none; }
        .info-box .info-box-text, .info-box .info-box-text a { color: #9aa5b1; }
        .info-box .info-box-number { color: #f1f5f9; font-size: 22px; }
        .info-box .text-muted { color: #9aa5b1 !important; }
        .box .progress { background: #2b3640; }
    </style>
    {!! Theme::js('vendor/chartjs/chart.min.js') !!}
    <script>
        (function () {
            var stats = {{ \Illuminate\Support\Js::from(['registrations' => $stats['registrations'], 'earned' => $stats['coins_earned'], 'spent' => $stats['coins_spent']]) }};
            var T = {{ \Illuminate\Support\Js::from(trans('admin/index.stats.series')) }};
            var axes = { yAxes: [{ ticks: { beginAtZero: true, precision: 0, fontColor: '#aaa' }, gridLines: { color: 'rgba(255,255,255,0.05)' } }], xAxes: [{ ticks: { fontColor: '#aaa', maxTicksLimit: 10 }, gridLines: { display: false } }] };

            new Chart(document.getElementById('chart-registrations'), {
                type: 'bar',
                data: { labels: stats.registrations.labels, datasets: [{ label: T.registrations, data: stats.registrations.values, backgroundColor: 'rgba(60,141,188,0.7)' }] },
                options: { legend: { display: false }, scales: axes }
            });

            new Chart(document.getElementById('chart-coins'), {
                type: 'line',
                data: {
                    labels: stats.earned.labels,
                    datasets: [
                        { label: T.earned, data: stats.earned.values, borderColor: '#00a65a', backgroundColor: 'rgba(0,166,90,0.15)', pointRadius: 0, lineTension: 0.2 },
                        { label: T.spent, data: stats.spent.values, borderColor: '#dd4b39', backgroundColor: 'rgba(221,75,57,0.1)', pointRadius: 0, lineTension: 0.2 }
                    ]
                },
                options: { legend: { labels: { fontColor: '#ccc' } }, scales: axes }
            });
        })();
    </script>
@endsection
