@extends('layouts.admin')

@section('title')
    @lang('admin/vouchers.view.title', ['code' => $voucher->code])
@endsection

@section('content-header')
    <h1>{{ $voucher->code }}<small>@lang('admin/vouchers.view.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li><a href="{{ route('admin.vouchers') }}">@lang('admin/vouchers.heading')</a></li>
        <li class="active">{{ $voucher->code }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">@lang('admin/vouchers.view.details')</h3></div>
            <div class="box-body">
                <p><strong>@lang('admin/vouchers.list.coins'):</strong> {{ $voucher->coins }}</p>
                <p><strong>@lang('admin/vouchers.list.uses'):</strong> {{ $voucher->uses }} / {{ $voucher->max_uses ?? '∞' }}</p>
                <p><strong>@lang('admin/vouchers.list.expires'):</strong> {{ $voucher->expires_at ? $voucher->expires_at->format('Y-m-d H:i') : '—' }}</p>
                @if($voucher->note)<p><strong>@lang('admin/vouchers.create.note_label'):</strong> {{ $voucher->note }}</p>@endif
                <p><strong>@lang('admin/vouchers.list.status'):</strong>
                    @if(!$voucher->active)<span class="label label-default">@lang('admin/vouchers.list.disabled')</span>
                    @elseif($voucher->isExpired())<span class="label label-warning">@lang('admin/vouchers.list.expired')</span>
                    @elseif($voucher->isExhausted())<span class="label label-warning">@lang('admin/vouchers.list.exhausted')</span>
                    @else<span class="label label-success">@lang('admin/vouchers.list.active')</span>@endif
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">@lang('admin/vouchers.view.redemptions')</h3></div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <th>@lang('admin/vouchers.view.user')</th>
                            <th class="text-center">@lang('admin/vouchers.list.coins')</th>
                            <th>@lang('admin/vouchers.view.when')</th>
                        </tr>
                        @forelse($redemptions as $redemption)
                            <tr>
                                <td>
                                    @if($redemption->user)
                                        @if(Auth::user()->hasStaffPermission('users.view'))<a href="{{ route('admin.users.view', $redemption->user->id) }}">{{ $redemption->user->username }}</a>@else{{ $redemption->user->username }}@endif
                                    @else — @endif
                                </td>
                                <td class="text-center">{{ $redemption->coins }}</td>
                                <td>{{ $redemption->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">@lang('admin/vouchers.view.empty')</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($redemptions->hasPages())
                <div class="box-footer with-border"><div class="col-md-12 text-center">{!! $redemptions->render() !!}</div></div>
            @endif
        </div>
    </div>
</div>
@endsection
