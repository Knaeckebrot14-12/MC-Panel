@extends('layouts.admin')

@section('title')
    @lang('admin/abuse.flags.title')
@endsection

@section('content-header')
    <h1>@lang('admin/abuse.flags.heading')<small>@lang('admin/abuse.flags.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/abuse.flags.heading')</li>
    </ol>
@endsection

@section('content')
@php
    $me = Auth::user();
    $canManage = $me->hasStaffPermission('servers.manage');
@endphp
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">{{ $resolved ? trans('admin/abuse.flags.resolved_heading') : trans('admin/abuse.flags.open_heading') }}</h3>
                <div class="box-tools">
                    @if($resolved)
                        <a href="{{ route('admin.abuse') }}" class="btn btn-sm btn-default">@lang('admin/abuse.flags.show_open') ({{ $openCount }})</a>
                    @else
                        <a href="{{ route('admin.abuse', ['show' => 'resolved']) }}" class="btn btn-sm btn-default">@lang('admin/abuse.flags.show_resolved')</a>
                    @endif
                    @if($me->isOwner())
                        <a href="{{ route('admin.settings.abuse') }}" class="btn btn-sm btn-default"><i class="fa fa-cog"></i> @lang('admin/abuse.flags.settings')</a>
                    @endif
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <th>@lang('admin/abuse.flags.table.server')</th>
                            <th>@lang('admin/abuse.flags.table.owner')</th>
                            <th>@lang('admin/abuse.flags.table.type')</th>
                            <th>@lang('admin/abuse.flags.table.details')</th>
                            <th>@lang('admin/abuse.flags.table.seen')</th>
                            <th class="text-right">@lang('admin/abuse.flags.table.actions')</th>
                        </tr>
                        @forelse($flags as $flag)
                            @php $server = $flag->server; @endphp
                            <tr>
                                <td>
                                    @if($server)
                                        <a href="{{ route('admin.servers.view', $server->id) }}"><strong>{{ $server->name }}</strong></a>
                                        @if($server->isSuspended())<span class="label label-warning">@lang('admin/abuse.flags.suspended')</span>@endif
                                        <br><small class="text-muted">{{ $server->node?->name }}</small>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if($server?->user)
                                        @if($me->hasStaffPermission('users.view'))<a href="{{ route('admin.users.view', $server->user->id) }}">{{ $server->user->username }}</a>@else{{ $server->user->username }}@endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td><span class="label label-{{ $flag->type === 'miner' ? 'danger' : 'warning' }}">@lang('admin/abuse.types.' . $flag->type)</span></td>
                                <td style="max-width: 460px; word-break: break-word;">{{ $flag->describe() }}</td>
                                <td style="white-space: nowrap;">
                                    <span title="{{ $flag->first_seen_at->format('Y-m-d H:i:s') }}">{{ trans('admin/abuse.flags.first_seen') }} {{ $flag->first_seen_at->diffForHumans() }}</span><br>
                                    <small class="text-muted" title="{{ $flag->last_seen_at->format('Y-m-d H:i:s') }}">{{ trans('admin/abuse.flags.last_seen') }} {{ $flag->last_seen_at->diffForHumans() }}</small>
                                    @if($flag->resolved_at)
                                        <br><small class="text-muted">{{ trans('admin/abuse.flags.resolved_by', ['user' => $flag->resolver?->username ?? '—']) }} {{ $flag->resolved_at->diffForHumans() }}</small>
                                    @endif
                                </td>
                                <td class="text-right" style="white-space: nowrap;">
                                    @if($server)
                                        <a href="{{ route('admin.servers.view', $server->id) }}" class="btn btn-xs btn-default"><i class="fa fa-external-link"></i> @lang('admin/abuse.flags.open_server')</a>
                                        @if(!$resolved && !$server->isSuspended())
                                            <a href="{{ route($canManage ? 'admin.servers.view.manage' : 'admin.servers.view', $server->id) }}" class="btn btn-xs btn-warning" title="@lang('admin/abuse.flags.suspend_hint')"><i class="fa fa-ban"></i> @lang('admin/abuse.flags.suspend')</a>
                                        @endif
                                    @endif
                                    @if(!$resolved)
                                        <form action="{{ route('admin.abuse.resolve', $flag->id) }}" method="POST" style="display: inline;">
                                            {!! csrf_field() !!}
                                            <button type="submit" class="btn btn-xs btn-success"><i class="fa fa-check"></i> @lang('admin/abuse.flags.resolve')</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">{{ $resolved ? trans('admin/abuse.flags.empty_resolved') : trans('admin/abuse.flags.empty') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($flags->hasPages())
                <div class="box-footer with-border"><div class="col-md-12 text-center">{!! $flags->render() !!}</div></div>
            @endif
        </div>
        <p class="text-muted small">@lang('admin/abuse.flags.footer', ['hours' => \Pterodactyl\Services\Abuse\AbuseScanService::COOLDOWN_HOURS])</p>
    </div>
</div>
@endsection
