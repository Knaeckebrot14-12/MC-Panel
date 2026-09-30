<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="60">
    <title>@lang('status.title', ['app' => config('app.name')])</title>
    <link rel="icon" type="image/png" href="/favicons/favicon-32x32.png">
    <style>
        :root { --bg: #0f1419; --card: #1b2229; --border: #2a333d; --text: #e6e9ec; --muted: #8a96a3; --green: #2fbf71; --yellow: #e5a50a; --red: #e5484d; --blue: #3b82f6; }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--bg); color: var(--text); font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; line-height: 1.5; }
        main { max-width: 860px; margin: 0 auto; padding: 40px 16px 60px; }
        header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 28px; }
        h1 { font-size: 22px; margin: 0; }
        a { color: var(--blue); text-decoration: none; }
        .overall { border-radius: 10px; padding: 18px 20px; font-weight: 600; font-size: 17px; margin-bottom: 24px; border: 1px solid; }
        .overall.operational { background: rgba(47,191,113,.1); border-color: rgba(47,191,113,.4); color: var(--green); }
        .overall.partial { background: rgba(229,165,10,.1); border-color: rgba(229,165,10,.4); color: var(--yellow); }
        .overall.outage { background: rgba(229,72,77,.1); border-color: rgba(229,72,77,.4); color: var(--red); }
        .notice { background: rgba(59,130,246,.1); border: 1px solid rgba(59,130,246,.4); border-radius: 10px; padding: 14px 18px; margin-bottom: 24px; white-space: pre-line; }
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; }
        .node { padding: 16px 20px; display: grid; grid-template-columns: 1fr auto; gap: 6px 16px; align-items: center; }
        .node + .node { border-top: 1px solid var(--border); }
        .name { font-weight: 600; }
        .meta { color: var(--muted); font-size: 13px; }
        .badge { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; }
        .dot { width: 9px; height: 9px; border-radius: 50%; display: inline-block; }
        .online .dot { background: var(--green); } .online { color: var(--green); }
        .offline .dot { background: var(--red); } .offline { color: var(--red); }
        .maintenance .dot { background: var(--yellow); } .maintenance { color: var(--yellow); }
        .bars { grid-column: 1 / -1; display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 4px; }
        .bar-label { display: flex; justify-content: space-between; font-size: 12px; color: var(--muted); }
        .bar { height: 6px; background: var(--border); border-radius: 3px; overflow: hidden; }
        .bar > span { display: block; height: 100%; background: var(--blue); }
        .empty { padding: 24px; color: var(--muted); text-align: center; }
        footer { margin-top: 20px; color: var(--muted); font-size: 12px; text-align: center; }
        @media (max-width: 520px) { .bars { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<main>
    <header>
        <h1>@lang('status.title', ['app' => config('app.name')])</h1>
        <a href="/">@lang('status.to_panel') &rarr;</a>
    </header>

    <div class="overall {{ $overall }}">@lang('status.overall.' . $overall)</div>

    @if($maintenance !== null)
        <div class="notice"><strong>@lang('status.maintenance')</strong>@if($maintenance)<br>{{ $maintenance }}@endif</div>
    @endif

    <div class="card">
        @forelse($nodes as $node)
            <div class="node">
                <div>
                    <div class="name">{{ $node['name'] }}</div>
                    <div class="meta">{{ $node['location'] }} · @lang('status.servers', ['count' => $node['servers']])</div>
                </div>
                <div class="badge {{ $node['state'] }}"><span class="dot"></span>@lang('status.states.' . $node['state'])</div>
                @if(!is_null($node['memory_percent']) || !is_null($node['disk_percent']))
                    <div class="bars">
                        @foreach(['memory_percent' => 'memory', 'disk_percent' => 'disk'] as $key => $label)
                            @if(!is_null($node[$key]))
                                <div>
                                    <div class="bar-label"><span>@lang('status.' . $label)</span><span>{{ $node[$key] }}%</span></div>
                                    <div class="bar"><span style="width: {{ $node[$key] }}%"></span></div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="empty">@lang('status.no_nodes')</div>
        @endforelse
    </div>

    <footer>@lang('status.checked', ['time' => $checkedAt->format('d.m.Y H:i')])</footer>
</main>
</body>
</html>
