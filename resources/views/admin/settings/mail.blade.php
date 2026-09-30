@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'mail'])

@section('title')
    @lang('admin/settings_mail.title')
@endsection

@section('content-header')
    <h1>@lang('admin/settings_mail.heading')<small>@lang('admin/settings_mail.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/settings.index.breadcrumb_settings')</li>
    </ol>
@endsection

@section('content')
    @yield('settings::nav')
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('admin/settings_mail.email_settings_heading')</h3>
                </div>
                @if($disabled)
                    <div class="box-body">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="alert alert-info no-margin-bottom">
                                    {!! trans('admin/settings_mail.disabled_notice', [
                                        'command' => '<code>php artisan p:environment:mail</code>',
                                        'env_var' => '<code>MAIL_DRIVER=smtp</code>',
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <form>
                        <div class="box-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="control-label">@lang('admin/settings_mail.smtp_host_label')</label>
                                    <div>
                                        <input required type="text" class="form-control" name="mail:mailers:smtp:host" value="{{ old('mail:mailers:smtp:host', config('mail.mailers.smtp.host')) }}" />
                                        <p class="text-muted small">@lang('admin/settings_mail.smtp_host_description')</p>
                                    </div>
                                </div>
                                <div class="form-group col-md-2">
                                    <label class="control-label">@lang('admin/settings_mail.smtp_port_label')</label>
                                    <div>
                                        <input required type="number" class="form-control" name="mail:mailers:smtp:port" value="{{ old('mail:mailers:smtp:port', config('mail.mailers.smtp.port')) }}" />
                                        <p class="text-muted small">@lang('admin/settings_mail.smtp_port_description')</p>
                                    </div>
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="control-label">@lang('admin/settings_mail.encryption_label')</label>
                                    <div>
                                        @php
                                            $encryption = old('mail:mailers:smtp:encryption', config('mail.mailers.smtp.encryption'));
                                        @endphp
                                        <select name="mail:mailers:smtp:encryption" class="form-control">
                                            <option value="" @if($encryption === '') selected @endif>@lang('admin/settings_mail.encryption_none')</option>
                                            <option value="tls" @if($encryption === 'tls') selected @endif>@lang('admin/settings_mail.encryption_tls')</option>
                                            <option value="ssl" @if($encryption === 'ssl') selected @endif>@lang('admin/settings_mail.encryption_ssl')</option>
                                        </select>
                                        <p class="text-muted small">@lang('admin/settings_mail.encryption_description')</p>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="control-label">@lang('admin/settings_mail.username_label') <span class="field-optional"></span></label>
                                    <div>
                                        <input type="text" class="form-control" name="mail:mailers:smtp:username" value="{{ old('mail:mailers:smtp:username', config('mail.mailers.smtp.username')) }}" />
                                        <p class="text-muted small">@lang('admin/settings_mail.username_description')</p>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="control-label">@lang('admin/settings_mail.password_label') <span class="field-optional"></span></label>
                                    <div>
                                        <input type="password" class="form-control" name="mail:mailers:smtp:password"/>
                                        <p class="text-muted small">{!! trans('admin/settings_mail.password_description', ['flag' => '<code>!e</code>']) !!}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <hr />
                                <div class="form-group col-md-6">
                                    <label class="control-label">@lang('admin/settings_mail.mail_from_label')</label>
                                    <div>
                                        <input required type="email" class="form-control" name="mail:from:address" value="{{ old('mail:from:address', config('mail.from.address')) }}" />
                                        <p class="text-muted small">@lang('admin/settings_mail.mail_from_description')</p>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="control-label">@lang('admin/settings_mail.mail_from_name_label') <span class="field-optional"></span></label>
                                    <div>
                                        <input type="text" class="form-control" name="mail:from:name" value="{{ old('mail:from:name', config('mail.from.name')) }}" />
                                        <p class="text-muted small">@lang('admin/settings_mail.mail_from_name_description')</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            {{ csrf_field() }}
                            <div class="pull-right">
                                <button type="button" id="testButton" class="btn btn-sm btn-success">@lang('admin/settings_mail.test_button')</button>
                                <button type="button" id="saveButton" class="btn btn-sm btn-primary">@lang('strings.save')</button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('footer-scripts')
    @parent

    <script>
        function saveSettings() {
            return $.ajax({
                method: 'PATCH',
                url: '/admin/settings/mail',
                contentType: 'application/json',
                data: JSON.stringify({
                    'mail:mailers:smtp:host': $('input[name="mail:mailers:smtp:host"]').val(),
                    'mail:mailers:smtp:port': $('input[name="mail:mailers:smtp:port"]').val(),
                    'mail:mailers:smtp:encryption': $('select[name="mail:mailers:smtp:encryption"]').val(),
                    'mail:mailers:smtp:username': $('input[name="mail:mailers:smtp:username"]').val(),
                    'mail:mailers:smtp:password': $('input[name="mail:mailers:smtp:password"]').val(),
                    'mail:from:address': $('input[name="mail:from:address"]').val(),
                    'mail:from:name': $('input[name="mail:from:name"]').val()
                }),
                headers: { 'X-CSRF-Token': $('input[name="_token"]').val() }
            }).fail(function (jqXHR) {
                showErrorDialog(jqXHR, 'save');
            });
        }

        function testSettings() {
            swal({
                type: 'info',
                title: '{{ trans('admin/settings_mail.js.test_dialog_title') }}',
                text: '{{ trans('admin/settings_mail.js.test_dialog_text') }}',
                showCancelButton: true,
                confirmButtonText: '{{ trans('admin/settings_mail.test_button') }}',
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, function () {
                $.ajax({
                    method: 'POST',
                    url: '/admin/settings/mail/test',
                    headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() }
                }).fail(function (jqXHR) {
                    showErrorDialog(jqXHR, 'test');
                }).done(function () {
                    swal({
                        title: '{{ trans('strings.success') }}',
                        text: '{{ trans('admin/settings_mail.js.test_success_text') }}',
                        type: 'success'
                    });
                });
            });
        }

        function saveAndTestSettings() {
            saveSettings().done(testSettings);
        }

        function showErrorDialog(jqXHR, verb) {
            console.error(jqXHR);
            var errorText = '';
            if (!jqXHR.responseJSON) {
                errorText = jqXHR.responseText;
            } else if (jqXHR.responseJSON.error) {
                errorText = jqXHR.responseJSON.error;
            } else if (jqXHR.responseJSON.errors) {
                $.each(jqXHR.responseJSON.errors, function (i, v) {
                    if (v.detail) {
                        errorText += v.detail + ' ';
                    }
                });
            }

            var errorMessages = {
                save: '{{ trans('admin/settings_mail.js.error_text_save', ['error' => '']) }}',
                test: '{{ trans('admin/settings_mail.js.error_text_test', ['error' => '']) }}'
            };

            swal({
                title: '{{ trans('admin/settings_mail.js.error_title') }}',
                text: errorMessages[verb] + errorText,
                type: 'error'
            });
        }

        $(document).ready(function () {
            $('#testButton').on('click', saveAndTestSettings);
            $('#saveButton').on('click', function () {
                saveSettings().done(function () {
                    swal({
                        title: '{{ trans('strings.success') }}',
                        text: '{{ trans('admin/settings_mail.js.save_success_text') }}',
                        type: 'success'
                    });
                });
            });
        });
    </script>
@endsection
