@extends('layouts.admin')

@section('title')
    @lang('admin/servers_view.about.title', ['name' => $server->name])
@endsection

@section('content-header')
    <h1>{{ $server->name }}<small>{{ str_limit($server->description) }}</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li><a href="{{ route('admin.servers') }}">@lang('admin/servers.breadcrumb_servers')</a></li>
        <li class="active">{{ $server->name }}</li>
    </ol>
@endsection

@section('content')
@include('admin.servers.partials.navigation')
<div class="row">
    <div class="col-sm-8">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/servers_view.about.information_heading')</h3>
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover">
                            <tr>
                                <td>@lang('admin/servers_view.about.internal_identifier_label')</td>
                                <td><code>{{ $server->id }}</code></td>
                            </tr>
                            <tr>
                                <td>@lang('admin/servers_view.about.external_identifier_label')</td>
                                @if(is_null($server->external_id))
                                    <td><span class="label label-default">@lang('admin/servers_view.about.not_set')</span></td>
                                @else
                                    <td><code>{{ $server->external_id }}</code></td>
                                @endif
                            </tr>
                            <tr>
                                <td>@lang('admin/servers_view.about.uuid_label')</td>
                                <td><code>{{ $server->uuid }}</code></td>
                            </tr>
                            <tr>
                                <td>@lang('admin/servers_view.about.current_egg_label')</td>
                                <td>
                                    @if(Auth::user()->isOwner())
                                    <a href="{{ route('admin.nests.view', $server->nest_id) }}">{{ $server->nest->name }}</a> ::
                                    <a href="{{ route('admin.nests.egg.view', $server->egg_id) }}">{{ $server->egg->name }}</a>
                                    @else{{ $server->nest->name }} :: {{ $server->egg->name }}@endif
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('admin/servers_view.about.server_name_label')</td>
                                <td>{{ $server->name }}</td>
                            </tr>
                            <tr>
                                <td>@lang('admin/servers_view.about.cpu_limit_label')</td>
                                <td>
                                    @if($server->cpu === 0)
                                        <code>@lang('admin/servers_view.about.unlimited')</code>
                                    @else
                                        <code>{{ $server->cpu }}%</code>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('admin/servers_view.about.cpu_pinning_label')</td>
                                <td>
                                    @if($server->threads != null)
                                        <code>{{ $server->threads }}</code>
                                    @else
                                        <span class="label label-default">@lang('admin/servers_view.about.not_set')</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('admin/servers_view.about.memory_label')</td>
                                <td>
                                    @if($server->memory === 0)
                                        <code>@lang('admin/servers_view.about.unlimited')</code>
                                    @else
                                        <code>{{ $server->memory }}MiB</code>
                                    @endif
                                    /
                                    @if($server->swap === 0)
                                        <code data-toggle="tooltip" data-placement="top" title="@lang('admin/servers_view.about.swap_tooltip')">@lang('admin/servers_view.about.not_set')</code>
                                    @elseif($server->swap === -1)
                                        <code data-toggle="tooltip" data-placement="top" title="@lang('admin/servers_view.about.swap_tooltip')">@lang('admin/servers_view.about.unlimited')</code>
                                    @else
                                        <code data-toggle="tooltip" data-placement="top" title="@lang('admin/servers_view.about.swap_tooltip')"> {{ $server->swap }}MiB</code>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('admin/servers_view.about.disk_space_label')</td>
                                <td>
                                    @if($server->disk === 0)
                                        <code>@lang('admin/servers_view.about.unlimited')</code>
                                    @else
                                        <code>{{ $server->disk }}MiB</code>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('admin/servers_view.about.block_io_weight_label')</td>
                                <td><code>{{ $server->io }}</code></td>
                            </tr>
                            <tr>
                                <td>@lang('admin/servers_view.about.default_connection_label')</td>
                                <td><code>{{ $server->allocation->ip }}:{{ $server->allocation->port }}</code></td>
                            </tr>
                            <tr>
                                <td>@lang('admin/servers_view.about.connection_alias_label')</td>
                                <td>
                                    @if($server->allocation->alias !== $server->allocation->ip)
                                        <code>{{ $server->allocation->alias }}:{{ $server->allocation->port }}</code>
                                    @else
                                        <span class="label label-default">@lang('admin/servers_view.about.no_alias_assigned')</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="box box-primary">
            <div class="box-body" style="padding-bottom: 0px;">
                <div class="row">
                    @if($server->isSuspended())
                        <div class="col-sm-12">
                            <div class="small-box bg-yellow">
                                <div class="inner">
                                    <h3 class="no-margin">@lang('admin/servers_view.about.status_suspended')</h3>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if(!Auth::user()->root_admin && Auth::user()->hasStaffPermission('servers.moderate'))
                        <div class="col-sm-12">
                            <form action="{{ route('admin.servers.view.manage.suspension', $server->id) }}" method="POST" style="margin-bottom: 20px;">
                                {!! csrf_field() !!}
                                <input type="hidden" name="action" value="{{ $server->isSuspended() ? 'unsuspend' : 'suspend' }}" />
                                <button type="submit" class="btn btn-block {{ $server->isSuspended() ? 'btn-success' : 'btn-warning' }}">{{ $server->isSuspended() ? trans('admin/servers_view.manage.unsuspend_button') : trans('admin/servers_view.manage.suspend_button') }}</button>
                            </form>
                        </div>
                    @endif
                    @if(!$server->isInstalled())
                        <div class="col-sm-12">
                            <div class="small-box {{ (! $server->isInstalled()) ? 'bg-blue' : 'bg-maroon' }}">
                                <div class="inner">
                                    <h3 class="no-margin">{{ (! $server->isInstalled()) ? trans('admin/servers_view.about.status_installing') : trans('admin/servers_view.about.status_install_failed') }}</h3>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="col-sm-12">
                        <div class="small-box bg-gray">
                            <div class="inner">
                                <h3>{{ str_limit($server->user->username, 16) }}</h3>
                                <p>@lang('admin/servers_view.about.server_owner_label')</p>
                            </div>
                            <div class="icon"><i class="fa fa-user"></i></div>
                            <a href="{{ route('admin.users.view', $server->user->id) }}" class="small-box-footer">
                                @lang('admin/servers_view.about.more_info') <i class="fa fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="small-box bg-gray">
                            <div class="inner">
                                <h3>{{ str_limit($server->node->name, 16) }}</h3>
                                <p>@lang('admin/servers_view.about.server_node_label')</p>
                            </div>
                            <div class="icon"><i class="fa fa-codepen"></i></div>
                            @if(Auth::user()->isOwner())
                            <a href="{{ route('admin.nodes.view', $server->node->id) }}" class="small-box-footer">
                                @lang('admin/servers_view.about.more_info') <i class="fa fa-arrow-circle-right"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
