@extends('layouts.admin')

@section('title')
    @lang('admin/tickets.view.title', ['id' => $ticket->id])
@endsection

@section('content-header')
    <h1>#{{ $ticket->id }} {{ $ticket->subject }}<small>{{ $ticket->user->username }}</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ Auth::user()->root_admin ? route('admin.index') : route('admin.tickets') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li><a href="{{ route('admin.tickets') }}">@lang('admin/tickets.index.heading')</a></li>
        <li class="active">#{{ $ticket->id }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        @foreach($ticket->messages as $message)
            <div class="box {{ $message->is_internal ? 'box-warning' : ($message->is_staff ? 'box-primary' : 'box-default') }}">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        {{ optional($message->author)->username ?? '—' }}
                        @if($message->is_internal)<span class="label label-warning">@lang('admin/tickets.view.internal_note')</span>
                        @elseif($message->is_staff)<span class="label label-primary">@lang('admin/tickets.view.staff')</span>@endif
                    </h3>
                    <div class="box-tools"><small class="text-muted">{{ $message->created_at->format('Y-m-d H:i') }}</small></div>
                </div>
                <div class="box-body" style="white-space: pre-wrap;">{{ $message->body }}</div>
            </div>
        @endforeach

        <div class="box box-success">
            <div class="box-header with-border"><h3 class="box-title">@lang('admin/tickets.view.reply_heading')</h3></div>
            <form action="{{ route('admin.tickets.reply', $ticket->id) }}" method="POST">
                <div class="box-body">
                    <textarea name="message" rows="6" class="form-control" required maxlength="5000">{{ old('message') }}</textarea>
                    <div class="checkbox checkbox-primary">
                        <input id="ticketInternal" type="checkbox" name="internal" value="1">
                        <label for="ticketInternal">@lang('admin/tickets.view.internal_checkbox')</label>
                    </div>
                    <div class="form-group" style="margin-top: 10px;">
                        <label class="control-label">@lang('admin/tickets.view.after_label')</label>
                        <select name="after" class="form-control">
                            <option value="answered">@lang('admin/tickets.view.after_answered')</option>
                            <option value="closed">@lang('admin/tickets.view.after_closed')</option>
                            <option value="keep">@lang('admin/tickets.view.after_keep')</option>
                        </select>
                    </div>
                </div>
                <div class="box-footer">
                    {!! csrf_field() !!}
                    <button type="submit" class="btn btn-success pull-right">@lang('admin/tickets.view.send')</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">@lang('admin/tickets.view.details_heading')</h3></div>
            <form action="{{ route('admin.tickets.update', $ticket->id) }}" method="POST">
                <div class="box-body">
                    <p><strong>@lang('admin/tickets.table.user'):</strong>
                        @if(Auth::user()->hasStaffPermission('users.view'))<a href="{{ route('admin.users.view', $ticket->user->id) }}">{{ $ticket->user->username }}</a>@else{{ $ticket->user->username }}@endif
                        <small class="text-muted">({{ Auth::user()->visibleEmail($ticket->user) }})</small></p>
                    <p><strong>@lang('admin/tickets.table.category'):</strong> @lang('tickets.categories.' . $ticket->category)</p>
                    @if($ticket->server)
                        <p><strong>@lang('admin/tickets.view.server'):</strong>
                            @if(Auth::user()->hasStaffPermission('servers.view'))<a href="{{ route('admin.servers.view', $ticket->server->id) }}">{{ $ticket->server->name }}</a>@else{{ $ticket->server->name }}@endif</p>
                    @endif
                    <p><strong>@lang('admin/tickets.view.created'):</strong> {{ $ticket->created_at->format('Y-m-d H:i') }}</p>
                    @if($ticket->rating)
                        <p><strong>@lang('admin/tickets.view.rating'):</strong>
                            @if($ticket->rating > 0)<span class="label label-success"><i class="fa fa-thumbs-up"></i> @lang('admin/tickets.rating.up')</span>
                            @else<span class="label label-danger"><i class="fa fa-thumbs-down"></i> @lang('admin/tickets.rating.down')</span>@endif
                        </p>
                    @endif
                    <div class="form-group">
                        <label class="control-label">@lang('admin/tickets.table.status')</label>
                        <select name="status" class="form-control">
                            @foreach(\Pterodactyl\Models\Ticket::STATUSES as $s)
                                <option value="{{ $s }}" @if($ticket->status === $s) selected @endif>@lang('admin/tickets.status.' . $s)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">@lang('admin/tickets.table.priority')</label>
                        <select name="priority" class="form-control">
                            @foreach(\Pterodactyl\Models\Ticket::PRIORITIES as $p)
                                <option value="{{ $p }}" @if($ticket->priority === $p) selected @endif>@lang('tickets.priorities.' . $p)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">@lang('admin/tickets.table.assignee')</label>
                        <select name="assigned_to" class="form-control">
                            <option value="">@lang('admin/tickets.filters.unassigned')</option>
                            @foreach($staff->filter(function ($s) { return $s->hasStaffPermission('tickets'); }) as $member)
                                <option value="{{ $member->id }}" @if($ticket->assigned_to === $member->id) selected @endif>{{ $member->username }} ({{ trans('admin/users.roles.' . $member->effectiveRole()) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="box-footer">
                    {!! csrf_field() !!}
                    {!! method_field('PATCH') !!}
                    <button type="submit" class="btn btn-primary btn-sm pull-right">@lang('admin/tickets.view.save')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
