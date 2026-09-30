@extends('layouts.admin')

@section('title')
    @lang('admin/vouchers.title')
@endsection

@section('content-header')
    <h1>@lang('admin/vouchers.heading')<small>@lang('admin/vouchers.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/vouchers.heading')</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="box box-success">
            <div class="box-header with-border"><h3 class="box-title">@lang('admin/vouchers.create.heading')</h3></div>
            <form action="{{ route('admin.vouchers') }}" method="POST">
                <div class="box-body">
                    <div class="form-group">
                        <label class="control-label">@lang('admin/vouchers.create.code_label') <span class="field-optional"></span></label>
                        <input type="text" name="code" value="{{ old('code') }}" maxlength="64" class="form-control" placeholder="SUMMER2026">
                        <p class="text-muted small">@lang('admin/vouchers.create.code_description')</p>
                    </div>
                    <div class="form-group">
                        <label class="control-label">@lang('admin/vouchers.create.quantity_label') <span class="field-optional"></span></label>
                        <input type="number" name="quantity" min="1" max="100" value="{{ old('quantity', 1) }}" class="form-control">
                        <p class="text-muted small">@lang('admin/vouchers.create.quantity_description')</p>
                    </div>
                    <div class="form-group">
                        <label class="control-label">@lang('admin/vouchers.create.coins_label')</label>
                        <input type="number" name="coins" min="1" value="{{ old('coins', 100) }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label">@lang('admin/vouchers.create.max_uses_label') <span class="field-optional"></span></label>
                        <input type="number" name="max_uses" min="1" value="{{ old('max_uses') }}" class="form-control">
                        <p class="text-muted small">@lang('admin/vouchers.create.max_uses_description')</p>
                    </div>
                    <div class="form-group">
                        <label class="control-label">@lang('admin/vouchers.create.expires_label') <span class="field-optional"></span></label>
                        <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="control-label">@lang('admin/vouchers.create.note_label') <span class="field-optional"></span></label>
                        <input type="text" name="note" maxlength="191" value="{{ old('note') }}" class="form-control">
                    </div>
                </div>
                <div class="box-footer">
                    {!! csrf_field() !!}
                    <button type="submit" class="btn btn-success btn-sm pull-right">@lang('admin/vouchers.create.button')</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">@lang('admin/vouchers.list.heading')</h3></div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <th>@lang('admin/vouchers.list.code')</th>
                            <th class="text-center">@lang('admin/vouchers.list.coins')</th>
                            <th class="text-center">@lang('admin/vouchers.list.uses')</th>
                            <th>@lang('admin/vouchers.list.expires')</th>
                            <th class="text-center">@lang('admin/vouchers.list.status')</th>
                            <th></th>
                        </tr>
                        @forelse($vouchers as $voucher)
                            <tr>
                                <td><a href="{{ route('admin.vouchers.view', $voucher->id) }}"><code>{{ $voucher->code }}</code></a>@if($voucher->note)<br><small class="text-muted">{{ $voucher->note }}</small>@endif</td>
                                <td class="text-center">{{ $voucher->coins }}</td>
                                <td class="text-center">{{ $voucher->uses }} / {{ $voucher->max_uses ?? '∞' }}</td>
                                <td>{{ $voucher->expires_at ? $voucher->expires_at->format('Y-m-d H:i') : '—' }}</td>
                                <td class="text-center">
                                    @if(!$voucher->active)<span class="label label-default">@lang('admin/vouchers.list.disabled')</span>
                                    @elseif($voucher->isExpired())<span class="label label-warning">@lang('admin/vouchers.list.expired')</span>
                                    @elseif($voucher->isExhausted())<span class="label label-warning">@lang('admin/vouchers.list.exhausted')</span>
                                    @else<span class="label label-success">@lang('admin/vouchers.list.active')</span>@endif
                                </td>
                                <td class="text-right" style="white-space: nowrap;">
                                    <form action="{{ route('admin.vouchers.toggle', $voucher->id) }}" method="POST" style="display:inline;">
                                        {!! csrf_field() !!}
                                        <button class="btn btn-xs btn-default">{{ $voucher->active ? trans('admin/vouchers.list.disable') : trans('admin/vouchers.list.enable') }}</button>
                                    </form>
                                    <form action="{{ route('admin.vouchers.delete', $voucher->id) }}" method="POST" style="display:inline;">
                                        {!! csrf_field() !!}
                                        {!! method_field('DELETE') !!}
                                        <button class="btn btn-xs btn-danger"><i class="fa fa-trash-o"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">@lang('admin/vouchers.list.empty')</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($vouchers->hasPages())
                <div class="box-footer with-border"><div class="col-md-12 text-center">{!! $vouchers->render() !!}</div></div>
            @endif
        </div>
    </div>
</div>
@endsection
