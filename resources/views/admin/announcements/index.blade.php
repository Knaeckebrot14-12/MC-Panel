@extends('layouts.admin')

@section('title')
    @lang('admin/announcements.title')
@endsection

@section('content-header')
    <h1>@lang('admin/announcements.heading')<small>@lang('admin/announcements.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/announcements.heading')</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('admin/announcements.list_heading')</h3>
                <div class="box-tools">
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#newAnnouncementModal">@lang('admin/announcements.create_new_button')</button>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <th>@lang('admin/announcements.table.title')</th>
                            <th>@lang('admin/announcements.table.content')</th>
                            <th class="text-center">@lang('admin/announcements.table.status')</th>
                            <th class="text-center">@lang('admin/announcements.table.created')</th>
                            <th></th>
                        </tr>
                        @forelse ($announcements as $announcement)
                            <tr>
                                <td>{{ $announcement->title }}</td>
                                <td style="max-width: 420px;">{{ Str::limit($announcement->content, 120) }}</td>
                                <td class="text-center">
                                    @if ($announcement->is_active)
                                        <span class="label label-success">@lang('admin/announcements.status_active')</span>
                                    @else
                                        <span class="label label-default">@lang('admin/announcements.status_hidden')</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $announcement->created_at->toDateTimeString() }}</td>
                                <td class="text-center" style="white-space: nowrap;">
                                    <form action="{{ route('admin.announcements.toggle', $announcement->id) }}" method="POST" style="display: inline-block;">
                                        {!! csrf_field() !!}
                                        <button type="submit" class="btn btn-xs btn-default">
                                            {{ $announcement->is_active ? trans('admin/announcements.hide_button') : trans('admin/announcements.show_button') }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.announcements.delete', $announcement->id) }}" method="POST" style="display: inline-block;" class="js-delete-announcement-form">
                                        {!! csrf_field() !!}
                                        {!! method_field('DELETE') !!}
                                        <button type="submit" class="btn btn-xs btn-danger">@lang('admin/announcements.delete_button')</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">@lang('admin/announcements.empty')</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="newAnnouncementModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.announcements') }}" method="POST">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="@lang('admin/announcements.modal.close_aria')"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">@lang('admin/announcements.modal.title')</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="pTitleModal" class="form-label">@lang('admin/announcements.modal.title_label')</label>
                            <input type="text" name="title" id="pTitleModal" class="form-control" maxlength="191" required />
                        </div>
                        <div class="col-md-12" style="margin-top: 10px;">
                            <label for="pContentModal" class="form-label">@lang('admin/announcements.modal.message_label')</label>
                            <textarea name="content" id="pContentModal" class="form-control" rows="4" maxlength="2000" required></textarea>
                            <p class="text-muted small">@lang('admin/announcements.modal.message_description')</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    {!! csrf_field() !!}
                    <button type="button" class="btn btn-default btn-sm pull-left" data-dismiss="modal">@lang('admin/announcements.modal.cancel_button')</button>
                    <button type="submit" class="btn btn-success btn-sm">@lang('admin/announcements.modal.create_button')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    (function () {
        document.querySelectorAll('.js-delete-announcement-form button[type="submit"]').forEach(function (button) {
            var confirming = false;
            var originalText = button.textContent;
            var resetTimer;

            button.addEventListener('click', function (e) {
                if (confirming) {
                    return;
                }

                e.preventDefault();
                confirming = true;
                button.textContent = '{{ trans('admin/announcements.delete_confirm_button') }}';

                resetTimer = setTimeout(function () {
                    confirming = false;
                    button.textContent = originalText;
                }, 3000);
            });
        });
    })();
    </script>
@endsection
