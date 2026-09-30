@extends('layouts.admin')

@section('title')
    @lang('admin/tickets.index.title')
@endsection

@section('content-header')
    <h1>@lang('admin/tickets.index.heading')<small>@lang('admin/tickets.index.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ Auth::user()->root_admin ? route('admin.index') : route('admin.tickets') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/tickets.index.heading')</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('admin/tickets.index.list_heading')@if($ratingStats['total'] > 0) <small>{{ trans('admin/tickets.index.rating_summary', ['percent' => $ratingStats['percent'], 'total' => $ratingStats['total']]) }}</small>@endif</h3>
                <div class="box-tools">
                    <form action="{{ route('admin.tickets') }}" method="GET" class="form-inline">
                        <select name="status" class="form-control input-sm" onchange="this.form.submit()">
                            <option value="active" @if($filters['status'] === 'active') selected @endif>@lang('admin/tickets.filters.active')</option>
                            <option value="all" @if($filters['status'] === 'all') selected @endif>@lang('admin/tickets.filters.all')</option>
                            @foreach(\Pterodactyl\Models\Ticket::STATUSES as $s)
                                <option value="{{ $s }}" @if($filters['status'] === $s) selected @endif>@lang('admin/tickets.status.' . $s)</option>
                            @endforeach
                        </select>
                        <select name="assigned" class="form-control input-sm" onchange="this.form.submit()">
                            <option value="">@lang('admin/tickets.filters.any_assignee')</option>
                            <option value="me" @if($filters['assigned'] === 'me') selected @endif>@lang('admin/tickets.filters.mine')</option>
                            <option value="none" @if($filters['assigned'] === 'none') selected @endif>@lang('admin/tickets.filters.unassigned')</option>
                        </select>
                        <input type="text" name="search" value="{{ $filters['search'] }}" class="form-control input-sm" placeholder="@lang('admin/tickets.filters.search')">
                        <button type="submit" class="btn btn-sm btn-default"><i class="fa fa-search"></i></button>
                    </form>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <th>#</th>
                            <th>@lang('admin/tickets.table.subject')</th>
                            <th>@lang('admin/tickets.table.user')</th>
                            <th>@lang('admin/tickets.table.category')</th>
                            <th class="text-center">@lang('admin/tickets.table.priority')</th>
                            <th class="text-center">@lang('admin/tickets.table.status')</th>
                            <th>@lang('admin/tickets.table.assignee')</th>
                            <th>@lang('admin/tickets.table.last_reply')</th>
                            <th class="text-center">@lang('admin/tickets.table.rating')</th>
                        </tr>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td><code>{{ $ticket->id }}</code></td>
                                <td><a href="{{ route('admin.tickets.view', $ticket->id) }}">{{ $ticket->subject }}</a></td>
                                <td>{{ $ticket->user->username }}</td>
                                <td>@lang('tickets.categories.' . $ticket->category)</td>
                                <td class="text-center"><span class="label label-{{ ['low' => 'default', 'normal' => 'info', 'high' => 'warning', 'urgent' => 'danger'][$ticket->priority] }}">@lang('tickets.priorities.' . $ticket->priority)</span></td>
                                <td class="text-center"><span class="label label-{{ ['open' => 'warning', 'customer_reply' => 'danger', 'answered' => 'success', 'closed' => 'default'][$ticket->status] }}">@lang('admin/tickets.status.' . $ticket->status)</span></td>
                                <td>{{ optional($ticket->assignee)->username ?? '—' }}</td>
                                <td>{{ optional($ticket->last_reply_at)->diffForHumans() }}</td>
                                <td class="text-center">@if($ticket->rating > 0)<i class="fa fa-thumbs-up text-green"></i>@elseif($ticket->rating < 0)<i class="fa fa-thumbs-down text-red"></i>@else<span class="text-muted">—</span>@endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted">@lang('admin/tickets.index.empty')</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tickets->hasPages())
                <div class="box-footer with-border"><div class="col-md-12 text-center">{!! $tickets->render() !!}</div></div>
            @endif
        </div>
    </div>
</div>
@endsection
