@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'roles'])

@section('title')
    @lang('admin/roles.title')
@endsection

@section('content-header')
    <h1>@lang('admin/roles.title')<small>@lang('admin/roles.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/roles.title')</li>
    </ol>
@endsection

@section('content')
    @yield('settings::nav')
    <form action="{{ route('admin.settings.roles') }}" method="POST">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin/roles.matrix_heading')</h3>
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover" id="role-matrix">
                            <thead>
                                <tr>
                                    <th style="width:45%;">@lang('admin/roles.permission')</th>
                                    @foreach($roles as $role)
                                        <th class="text-center">
                                            @lang('admin/users.roles.' . $role)
                                            <br><small class="text-muted">@lang('admin/roles.members', ['count' => $counts[$role] ?? 0])</small>
                                        </th>
                                    @endforeach
                                    <th class="text-center">@lang('admin/users.roles.owner')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groups as $group => $permissions)
                                    <tr style="background:rgba(127,127,127,0.18);">
                                        <td colspan="{{ count($roles) + 2 }}"><strong>@lang("admin/roles.groups.$group")</strong></td>
                                    </tr>
                                    @foreach($permissions as $permission)
                                        <tr>
                                            <td>
                                                @lang('admin/roles.permissions.' . str_replace('.', '_', $permission) . '.name')
                                                <br><small class="text-muted">@lang('admin/roles.permissions.' . str_replace('.', '_', $permission) . '.description')</small>
                                            </td>
                                            @foreach($roles as $role)
                                                <td class="text-center" style="vertical-align:middle;">
                                                    <input type="checkbox" name="permissions[{{ $role }}][]" value="{{ $permission }}"
                                                           @if(in_array($permission, $matrix[$role], true)) checked @endif>
                                                </td>
                                            @endforeach
                                            <td class="text-center text-muted" style="vertical-align:middle;"><i class="fa fa-check"></i></td>
                                        </tr>
                                    @endforeach
                                @endforeach
                                <tr style="background:rgba(127,127,127,0.18);">
                                    <td colspan="{{ count($roles) + 2 }}"><strong>@lang('admin/roles.groups.owner_only')</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="{{ count($roles) + 1 }}" class="text-muted small">@lang('admin/roles.owner_only_text')</td>
                                    <td class="text-center text-muted" style="vertical-align:middle;"><i class="fa fa-check"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="box-footer">
                        {!! csrf_field() !!}
                        {!! method_field('PATCH') !!}
                        <p class="text-muted small pull-left" style="max-width:70%;margin:6px 0 0;">@lang('admin/roles.hint')</p>
                        <button type="submit" class="btn btn-sm btn-primary pull-right">@lang('admin/roles.save')</button>
                        <button type="submit" name="reset" value="1" class="btn btn-sm btn-default pull-right" style="margin-right:8px;"
                                onclick="return confirm('{{ e(trans('admin/roles.reset_confirm')) }}');">@lang('admin/roles.reset')</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
