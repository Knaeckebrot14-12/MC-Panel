@extends('layouts.admin')

@section('title')
    @lang('admin/users.index.title')
@endsection

@section('content-header')
    <h1>@lang('admin/users.index.heading')<small>@lang('admin/users.index.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/users.breadcrumb_users')</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('admin/users.index.list_heading')</h3>
                <div class="box-tools search01">
                    <form action="{{ route('admin.users') }}" method="GET">
                        <div class="input-group input-group-sm">
                            <span class="input-group-btn" style="width: auto;">
                                <select name="role" class="form-control" style="width: auto; border-radius: 3px 0 0 3px;" onchange="this.form.submit()">
                                    <option value="">@lang('admin/users.index.filter_all_roles')</option>
                                    @foreach(\Pterodactyl\Models\User::ROLES as $roleKey => $rank)
                                        <option value="{{ $roleKey }}" @if($roleFilter === $roleKey) selected @endif>@lang('admin/users.roles.' . $roleKey)</option>
                                    @endforeach
                                </select>
                                <select name="sort" class="form-control" style="width: auto; border-radius: 0;" onchange="this.form.submit()">
                                    @foreach(['-root_admin' => 'sort_default', '-created_at' => 'sort_newest', 'created_at' => 'sort_oldest', 'username' => 'sort_username', '-coins' => 'sort_coins'] as $sortKey => $label)
                                        <option value="{{ $sortKey }}" @if(request('sort', '-root_admin') === $sortKey) selected @endif>@lang('admin/users.index.' . $label)</option>
                                    @endforeach
                                </select>
                            </span>
                            <input type="text" name="filter[email]" class="form-control pull-right" value="{{ request()->input('filter.email') }}" placeholder="@lang('admin/users.index.search_placeholder')">
                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                                @if(Auth::user()->hasStaffPermission('users.edit'))<a href="{{ route('admin.users.new') }}"><button type="button" class="btn btn-sm btn-primary" style="border-radius: 0 3px 3px 0;margin-left:-1px;">@lang('admin/users.index.create_new_button')</button></a>@endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>@lang('admin/users.index.table.id')</th>
                            @if($seesEmails)<th>@lang('admin/users.index.table.email')</th>@endif
                            <th>@lang('admin/users.index.table.client_name')</th>
                            <th>@lang('admin/users.index.table.username')</th>
                            <th class="text-center">@lang('admin/users.index.table.2fa')</th>
                            <th class="text-center"><span data-toggle="tooltip" data-placement="top" title="@lang('admin/users.index.table.servers_owned_tooltip')">@lang('admin/users.index.table.servers_owned')</span></th>
                            <th class="text-center"><span data-toggle="tooltip" data-placement="top" title="@lang('admin/users.index.table.can_access_tooltip')">@lang('admin/users.index.table.can_access')</span></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr class="align-middle">
                                <td><code>{{ $user->id }}</code></td>
                                @if($seesEmails)<td><a href="{{ route('admin.users.view', $user->id) }}">{{ $user->email }}</a> @if($user->isStaff())<span class="label label-{{ $user->isOwner() ? 'danger' : ($user->root_admin ? 'warning' : 'info') }}">@lang('admin/users.roles.' . $user->effectiveRole())</span>@endif</td>@endif
                                <td>{{ $user->name_last }}, {{ $user->name_first }}</td>
                                <td>@if($seesEmails){{ $user->username }}@else<a href="{{ route('admin.users.view', $user->id) }}">{{ $user->username }}</a> @if($user->isStaff())<span class="label label-{{ $user->isOwner() ? 'danger' : ($user->root_admin ? 'warning' : 'info') }}">@lang('admin/users.roles.' . $user->effectiveRole())</span>@endif @endif</td>
                                <td class="text-center">
                                    @if($user->use_totp)
                                        <i class="fa fa-lock text-green"></i>
                                    @else
                                        <i class="fa fa-unlock text-red"></i>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.servers', ['filter[owner_id]' => $user->id]) }}">{{ $user->servers_count }}</a>
                                </td>
                                <td class="text-center">{{ $user->subuser_of_count }}</td>
                                <td class="text-center"><img src="https://www.gravatar.com/avatar/{{ md5(strtolower($seesEmails ? $user->email : $user->username)) }}?s=100" style="height:20px;" class="img-circle" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="box-footer with-border">
                    <div class="col-md-12 text-center">{!! $users->render() !!}</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
