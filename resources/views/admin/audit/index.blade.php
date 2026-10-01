@extends('layouts.admin')

@section('title')
    @lang('admin/audit.title')
@endsection

@section('content-header')
    <h1>@lang('admin/audit.heading')<small>@lang('admin/audit.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/audit.heading')</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('admin/audit.list_heading')</h3>
                <div class="box-tools">
                    <form action="{{ route('admin.audit') }}" method="GET" class="form-inline">
                        <select name="group" class="form-control input-sm" onchange="this.form.submit()">
                            <option value="">@lang('admin/audit.filter_all')</option>
                            @foreach($groups as $g)
                                <option value="{{ $g }}" @if($filters['group'] === $g) selected @endif>@lang('admin/audit.groups.' . $g)</option>
                            @endforeach
                        </select>
                        <input type="text" name="search" value="{{ $filters['search'] }}" class="form-control input-sm" placeholder="@lang('admin/audit.search')">
                        <button type="submit" class="btn btn-sm btn-default"><i class="fa fa-search"></i></button>
                    </form>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <th>@lang('admin/audit.table.when')</th>
                            <th>@lang('admin/audit.table.who')</th>
                            <th>@lang('admin/audit.table.what')</th>
                            <th>@lang('admin/audit.table.server')</th>
                            <th>@lang('admin/audit.table.user')</th>
                            <th>@lang('admin/audit.table.ip')</th>
                        </tr>
                        @forelse($logs as $log)
                            <tr>
                                <td style="white-space: nowrap;">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>@if($log->user)@if(Auth::user()->hasStaffPermission('users.view'))<a href="{{ route('admin.users.view', $log->user->id) }}"><strong>{{ $log->user->username }}</strong></a>@else<strong>{{ $log->user->username }}</strong>@endif <span class="label label-default">@lang('admin/users.roles.' . $log->user->effectiveRole())</span>@else — @endif</td>
                                <td>{{ $log->describe() }}</td>
                                <td>
                                    @if($log->targetServer)@if(Auth::user()->hasStaffPermission('servers.view'))<a href="{{ route('admin.servers.view', $log->targetServer->id) }}">{{ $log->targetServer->name }}</a>@else{{ $log->targetServer->name }}@endif
                                    @elseif($log->target_server){{ $log->target_server }}
                                    @else — @endif
                                </td>
                                <td>
                                    @if($log->targetUser)@if(Auth::user()->hasStaffPermission('users.view'))<a href="{{ route('admin.users.view', $log->targetUser->id) }}">{{ $log->targetUser->username }}</a>@else{{ $log->targetUser->username }}@endif
                                    @elseif($log->target_user){{ $log->target_user }}
                                    @else — @endif
                                </td>
                                <td><code>{{ $log->ip ?? '—' }}</code></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">@lang('admin/audit.empty')</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
                <div class="box-footer with-border"><div class="col-md-12 text-center">{!! $logs->render() !!}</div></div>
            @endif
        </div>
    </div>
</div>
@endsection
