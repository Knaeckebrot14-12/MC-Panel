@extends('layouts.admin')

@section('title')
    @lang('admin/plans.edit_title', ['name' => $plan->name])
@endsection

@section('content-header')
    <h1>{{ $plan->name }}<small>@lang('admin/plans.edit_subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li><a href="{{ route('admin.plans') }}">@lang('admin/plans.heading')</a></li>
        <li class="active">{{ $plan->name }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <div class="box box-primary">
            <form action="{{ route('admin.plans.edit', $plan->id) }}" method="POST">
                @include('admin.plans._form')
                <div class="box-footer">
                    {!! csrf_field() !!}
                    {!! method_field('PATCH') !!}
                    <button type="submit" class="btn btn-primary btn-sm pull-right">@lang('admin/plans.save_button')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
