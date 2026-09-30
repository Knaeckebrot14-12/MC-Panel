@extends('layouts.admin')

@section('title')
    @lang('admin/users.new.title')
@endsection

@section('content-header')
    <h1>@lang('admin/users.new.heading')<small>@lang('admin/users.new.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li><a href="{{ route('admin.users') }}">@lang('admin/users.breadcrumb_users')</a></li>
        <li class="active">@lang('admin/users.new.breadcrumb_create')</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <form method="post">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/users.identity_heading')</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label for="email" class="control-label">@lang('admin/users.email_label')</label>
                        <div>
                            <input type="text" autocomplete="off" name="email" value="{{ old('email') }}" class="form-control" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="username" class="control-label">@lang('admin/users.username_label')</label>
                        <div>
                            <input type="text" autocomplete="off" name="username" value="{{ old('username') }}" class="form-control" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name_first" class="control-label">@lang('admin/users.first_name_label')</label>
                        <div>
                            <input type="text" autocomplete="off" name="name_first" value="{{ old('name_first') }}" class="form-control" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name_last" class="control-label">@lang('admin/users.last_name_label')</label>
                        <div>
                            <input type="text" autocomplete="off" name="name_last" value="{{ old('name_last') }}" class="form-control" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">@lang('admin/users.default_language_label')</label>
                        <div>
                            <select name="language" class="form-control">
                                @foreach($languages as $key => $value)
                                    <option value="{{ $key }}" @if(config('app.default_locale', config('app.locale')) === $key) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            <p class="text-muted"><small>@lang('admin/users.default_language_description')</small></p>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    {!! csrf_field() !!}
                    <input type="submit" value="@lang('admin/users.new.create_button')" class="btn btn-success btn-sm">
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/users.permissions_heading')</h3>
                </div>
                <div class="box-body">
                    <div class="form-group col-md-12">
                        <label for="role" class="control-label">@lang('admin/users.role_label')</label>
                        <div>
                            <select name="role" class="form-control">
                                @foreach(Auth::user()->assignableRoles() as $assignable)
                                    <option value="{{ $assignable }}">@lang('admin/users.roles.' . $assignable)</option>
                                @endforeach
                            </select>
                            <p class="text-muted"><small>@lang('admin/users.role_description')</small></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/users.password_heading')</h3>
                </div>
                <div class="box-body">
                    <div class="alert alert-info">
                        <p>@lang('admin/users.new.password_notice')</p>
                    </div>
                    <div id="gen_pass" class=" alert alert-success" style="display:none;margin-bottom: 10px;"></div>
                    <div class="form-group">
                        <label for="pass" class="control-label">@lang('admin/users.new.password_label')</label>
                        <div>
                            <input type="password" name="password" class="form-control" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>$("#gen_pass_bttn").click(function (event) {
            event.preventDefault();
            $.ajax({
                type: "GET",
                url: "/password-gen/12",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
               },
                success: function(data) {
                    $("#gen_pass").html('<strong>{{ trans('admin/users.new.generated_password_label') }}</strong> ' + data).slideDown();
                    $('input[name="password"], input[name="password_confirmation"]').val(data);
                    return false;
                }
            });
            return false;
        });
    </script>
@endsection
