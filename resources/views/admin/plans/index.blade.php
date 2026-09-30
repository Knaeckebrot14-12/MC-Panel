@extends('layouts.admin')

@section('title')
    @lang('admin/plans.title')
@endsection

@section('content-header')
    <h1>@lang('admin/plans.heading')<small>@lang('admin/plans.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/plans.heading')</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="box box-success">
            <div class="box-header with-border"><h3 class="box-title">@lang('admin/plans.create_heading')</h3></div>
            <form action="{{ route('admin.plans') }}" method="POST">
                @include('admin.plans._form')
                <div class="box-footer">
                    {!! csrf_field() !!}
                    <button type="submit" class="btn btn-success btn-sm pull-right">@lang('admin/plans.create_button')</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">@lang('admin/plans.list_heading')</h3></div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <th>@lang('admin/plans.form.name')</th>
                            <th class="text-center">@lang('admin/plans.form.memory')</th>
                            <th class="text-center">@lang('admin/plans.form.disk')</th>
                            <th class="text-center">@lang('admin/plans.form.cpu')</th>
                            <th class="text-center">@lang('admin/plans.form.backups')</th>
                            <th class="text-center">@lang('admin/plans.form.price')</th>
                            <th class="text-center">@lang('admin/plans.status')</th>
                            <th></th>
                        </tr>
                        @forelse($plans as $plan)
                            <tr>
                                <td><a href="{{ route('admin.plans.edit', $plan->id) }}">{{ $plan->name }}</a>@if($plan->description)<br><small class="text-muted">{{ $plan->description }}</small>@endif</td>
                                <td class="text-center">{{ $plan->memory }} MiB</td>
                                <td class="text-center">{{ $plan->disk }} MiB</td>
                                <td class="text-center">{{ $plan->cpu }}%</td>
                                <td class="text-center">{{ $plan->backups }}</td>
                                <td class="text-center">{{ $plan->monthly_price }}</td>
                                <td class="text-center"><span class="label label-{{ $plan->active ? 'success' : 'default' }}">{{ $plan->active ? trans('admin/plans.active') : trans('admin/plans.inactive') }}</span></td>
                                <td class="text-right" style="white-space: nowrap;">
                                    <a href="{{ route('admin.plans.edit', $plan->id) }}" class="btn btn-xs btn-primary"><i class="fa fa-wrench"></i></a>
                                    <form action="{{ route('admin.plans.delete', $plan->id) }}" method="POST" style="display:inline;">
                                        {!! csrf_field() !!}
                                        {!! method_field('DELETE') !!}
                                        <button class="btn btn-xs btn-danger"><i class="fa fa-trash-o"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">@lang('admin/plans.empty')</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="box-footer"><p class="text-muted small no-margin">@lang('admin/plans.price_note')</p></div>
        </div>
    </div>
</div>
@endsection
