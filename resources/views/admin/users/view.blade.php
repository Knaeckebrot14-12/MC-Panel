@extends('layouts.admin')

@section('title')
    @lang('admin/users.view.title', ['username' => $user->username])
@endsection

@section('content-header')
    <h1>{{ $user->name_first }} {{ $user->name_last}}<small>{{ $user->username }}</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li><a href="{{ route('admin.users') }}">@lang('admin/users.breadcrumb_users')</a></li>
        <li class="active">{{ $user->username }}</li>
    </ol>
@endsection

@section('content')
@php
    $me = Auth::user();
    $canEdit = $me->hasStaffPermission('users.edit');
    $canPassword = $me->hasStaffPermission('users.password') || $me->is($user);
@endphp
<div class="row">
@if($canEdit || $canPassword)
    <form action="{{ route('admin.users.view', $user->id) }}" method="post">
@if($canEdit)
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/users.identity_heading')</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label for="email" class="control-label">@lang('admin/users.email_label')</label>
                        <div>
                            @if($me->canSeeEmailOf($user))
                                <input type="email" name="email" value="{{ $user->email }}" class="form-control form-autocomplete-stop">
                            @else
                                <input type="text" value="@lang('admin/users.email_hidden')" class="form-control" disabled>
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="registered" class="control-label">@lang('admin/users.username_label')</label>
                        <div>
                            <input type="text" name="username" value="{{ $user->username }}" class="form-control form-autocomplete-stop">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="registered" class="control-label">@lang('admin/users.first_name_label')</label>
                        <div>
                            <input type="text" name="name_first" value="{{ $user->name_first }}" class="form-control form-autocomplete-stop">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="registered" class="control-label">@lang('admin/users.last_name_label')</label>
                        <div>
                            <input type="text" name="name_last" value="{{ $user->name_last }}" class="form-control form-autocomplete-stop">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">@lang('admin/users.default_language_label')</label>
                        <div>
                            <select name="language" class="form-control">
                                @foreach($languages as $key => $value)
                                    <option value="{{ $key }}" @if($user->language === $key) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            <p class="text-muted"><small>@lang('admin/users.default_language_description')</small></p>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    {!! csrf_field() !!}
                    {!! method_field('PATCH') !!}
                    <input type="submit" value="@lang('admin/users.view.update_button')" class="btn btn-primary btn-sm">
                </div>
            </div>
        </div>
@endif
@if($canPassword)
        <div class="col-md-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/users.password_heading')</h3>
                </div>
                <div class="box-body">
                    <div class="alert alert-success" style="display:none;margin-bottom:10px;" id="gen_pass"></div>
                    <div class="form-group no-margin-bottom">
                        <label for="password" class="control-label">@lang('admin/users.view.password_label') <span class="field-optional"></span></label>
                        <div>
                            <input type="password" id="password" name="password" class="form-control form-autocomplete-stop">
                            <p class="text-muted small">@lang('admin/users.view.password_keep_description')</p>
                        </div>
                    </div>
                </div>
                @if(!$canEdit)
                    <div class="box-footer">
                        {!! csrf_field() !!}
                        {!! method_field('PATCH') !!}
                        <input type="submit" value="@lang('admin/users.view.password_save_button')" class="btn btn-primary btn-sm">
                    </div>
                @endif
            </div>
        </div>
@endif
@if($canEdit)
        <div class="col-md-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/users.permissions_heading')</h3>
                </div>
                <div class="box-body">
                    <p class="no-margin">@lang('admin/users.view.roles.current'): <strong>@lang('admin/users.roles.' . $user->effectiveRole())</strong></p>
                </div>
            </div>
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/users.view.pool_heading')</h3>
                </div>
                <div class="box-body">
                    <p class="text-muted small">@lang('admin/users.view.pool_description')</p>
                    <div class="row">
                        <div class="col-xs-6 form-group">
                            <label class="control-label">@lang('admin/users.view.memory_label')</label>
                            <input type="number" min="0" name="server_memory_limit" value="{{ $user->server_memory_limit }}" class="form-control">
                        </div>
                        <div class="col-xs-6 form-group">
                            <label class="control-label">@lang('admin/users.view.disk_label')</label>
                            <input type="number" min="0" name="server_disk_limit" value="{{ $user->server_disk_limit }}" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6 form-group">
                            <label class="control-label">@lang('admin/users.view.cpu_label')</label>
                            <input type="number" min="0" name="server_cpu_limit" value="{{ $user->server_cpu_limit }}" class="form-control">
                        </div>
                        <div class="col-xs-6 form-group">
                            <label class="control-label">@lang('admin/users.view.backups_label')</label>
                            <input type="number" min="0" name="server_backup_limit" value="{{ $user->server_backup_limit }}" class="form-control">
                        </div>
                    </div>
                    <div class="form-group no-margin-bottom">
                        <label class="control-label">@lang('admin/users.view.server_slots_label')</label>
                        <input type="number" min="0" name="server_slots" value="{{ $user->server_slots }}" class="form-control">
                        <p class="text-muted small">@lang('admin/users.view.server_slots_description')</p>
                    </div>
                </div>
            </div>
        </div>
@endif
    </form>
@endif
    <div class="col-xs-12">
@if(count(Auth::user()->assignableRoles()) && Auth::user()->outranks($user))
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('admin/users.view.roles.heading')</h3>
            </div>
            <form action="{{ route('admin.users.role', $user->id) }}" method="POST" class="form-inline">
                <div class="box-body">
                    <p class="text-muted small">@lang('admin/users.view.roles.description')</p>
                    <p>@lang('admin/users.view.roles.current'): <strong>@lang('admin/users.roles.' . $user->effectiveRole())</strong></p>
                    <select name="role" class="form-control">
                        @foreach(Auth::user()->assignableRoles() as $assignable)
                            <option value="{{ $assignable }}" @if($user->effectiveRole() === $assignable) selected @endif>@lang('admin/users.roles.' . $assignable)</option>
                        @endforeach
                    </select>
                </div>
                <div class="box-footer">
                    {!! csrf_field() !!}
                    <button type="submit" class="btn btn-sm btn-primary pull-right">@lang('admin/users.view.roles.save')</button>
                </div>
            </form>
        </div>
@endif
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('admin/users.view.coins_heading')</h3>
            </div>
            <div class="box-body">
                <p class="no-margin">{!! trans('admin/users.view.coins_balance', ['balance' => '<strong>' . $user->coins . '</strong>']) !!}</p>
            </div>
@if($me->hasStaffPermission('users.coins'))
            <div class="box-footer">
                <form action="{{ route('admin.users.coins', $user->id) }}" method="POST" class="form-inline">
                    {!! csrf_field() !!}
                    <div class="form-group" style="margin-right: 10px;">
                        <label class="control-label" style="margin-right: 5px;">@lang('admin/users.view.coins_adjust_label')</label>
                        <input type="number" name="amount" value="0" class="form-control" style="width: 120px;" required>
                        <p class="text-muted small no-margin">@lang('admin/users.view.coins_adjust_description')</p>
                    </div>
                    <div class="form-group" style="margin-right: 10px;">
                        <label class="control-label" style="margin-right: 5px;">@lang('admin/users.view.coins_reason_label') <span class="field-optional"></span></label>
                        <input type="text" name="description" maxlength="191" class="form-control" style="width: 260px;">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">@lang('admin/users.view.coins_apply_button')</button>
                </form>
            </div>
@endif
        </div>
        <div class="box {{ $user->email_verified_at ? 'box-default' : 'box-warning' }}">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('admin/users.view.account_heading')</h3>
            </div>
            <div class="box-body">
                <dl class="dl-horizontal" style="margin-bottom:0;">
                    <dt>@lang('admin/users.view.email_status')</dt>
                    <dd>
                        @if($user->email_verified_at)
                            <span class="label label-success">@lang('admin/users.view.email_verified')</span>
                            <small class="text-muted">{{ $user->email_verified_at->diffForHumans() }}</small>
                        @else
                            <span class="label label-warning">@lang('admin/users.view.email_unverified')</span>
                        @endif
                    </dd>
                    <dt>@lang('admin/users.view.discord')</dt>
                    <dd>{{ $user->discord_username ? '@' . $user->discord_username : '—' }}</dd>
                    @if($me->canSeeEmailOf($user))
                        <dt>@lang('admin/users.view.registration_ip')</dt>
                        <dd><code>{{ $user->registration_ip ?: '—' }}</code></dd>
                    @endif
                    @if(count($sameIpUsers))
                        <dt>@lang('admin/users.view.same_ip')</dt>
                        <dd>
                            @foreach($sameIpUsers as $other)
                                <a href="{{ route('admin.users.view', $other->id) }}">{{ $other->username }}</a>@if(!$loop->last), @endif
                            @endforeach
                        </dd>
                    @endif
                </dl>
            </div>
            @if(!$user->email_verified_at && $me->hasStaffPermission('users.moderate'))
                <div class="box-footer">
                    <form action="{{ route('admin.users.verify-email', $user->id) }}" method="POST">
                        {!! csrf_field() !!}
                        <button type="submit" class="btn btn-sm btn-success pull-right">@lang('admin/users.view.verify_button')</button>
                    </form>
                </div>
            @endif
        </div>
@if($me->hasStaffPermission('users.moderate'))
        <div class="box {{ $user->isSuspended() ? 'box-success' : 'box-warning' }}">
            <div class="box-header with-border">
                <h3 class="box-title">{{ $user->isSuspended() ? trans('admin/users.view.unsuspend_heading') : trans('admin/users.view.suspend_heading') }}</h3>
            </div>
            <div class="box-body">
                @if ($user->isSuspended())
                    <p class="no-margin">{{ trans('admin/users.view.suspended_notice', ['time' => $user->suspended_at->diffForHumans()]) }}</p>
                @else
                    <p class="no-margin">@lang('admin/users.view.suspend_notice')</p>
                @endif
            </div>
            <div class="box-footer">
                <form action="{{ route($user->isSuspended() ? 'admin.users.unsuspend' : 'admin.users.suspend', $user->id) }}" method="POST">
                    {!! csrf_field() !!}
                    <button type="submit" class="btn btn-sm {{ $user->isSuspended() ? 'btn-success' : 'btn-warning' }} pull-right">
                        {{ $user->isSuspended() ? trans('admin/users.view.unsuspend_button') : trans('admin/users.view.suspend_button') }}
                    </button>
                </form>
            </div>
        </div>
@endif
@if($me->hasStaffPermission('users.delete'))
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('admin/users.view.delete_heading')</h3>
            </div>
            <div class="box-body">
                <p class="no-margin">@lang('admin/users.view.delete_notice')</p>
            </div>
            <div class="box-footer">
                <form action="{{ route('admin.users.view', $user->id) }}" method="POST">
                    {!! csrf_field() !!}
                    {!! method_field('DELETE') !!}
                    <input id="delete" type="submit" class="btn btn-sm btn-danger pull-right" {{ $user->servers->count() < 1 ?: 'disabled' }} value="@lang('admin/users.view.delete_button')" />
                </form>
            </div>
        </div>
@endif
    </div>
</div>
@endsection
