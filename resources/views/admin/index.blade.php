@extends('layouts.admin')

@section('title')
    @lang('admin/index.title')
@endsection

@section('content-header')
    <h1>@lang('admin/index.heading')<small>@lang('admin/index.subheading')</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">@lang('admin/layout.breadcrumb_admin')</a></li>
        <li class="active">@lang('admin/index.breadcrumb_index')</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        @php
            $updateInfo = app(\Pterodactyl\Services\Update\UpdateService::class)->summary();
            $installedLabel = $updateInfo['installed']['version'] . ($updateInfo['installed']['short'] ? ' (' . $updateInfo['installed']['short'] . ')' : '');
        @endphp
        <div class="box {{ $updateInfo['has_update'] ? 'box-warning' : 'box-success' }}">
            <div class="box-header with-border">
                <h3 class="box-title">@lang('admin/index.system_info_heading')</h3>
            </div>
            <div class="box-body">
                @if ($updateInfo['installed']['is_dev'])
                    @lang('admin/update.index.dev_build')
                @elseif ($updateInfo['has_update'])
                    {!! trans('admin/update.index.available', ['installed' => '<code>' . e($installedLabel) . '</code>', 'latest' => '<code>' . e(($updateInfo['latest']['version'] ?? '?') . ' (' . $updateInfo['latest']['short'] . ')') . '</code>']) !!}
                    @if(Auth::user()->effectiveRole() === 'owner')
                        <a href="{{ route('admin.settings.updates') }}" class="btn btn-warning btn-xs" style="margin-left:8px;">@lang('admin/update.index.open')</a>
                    @endif
                @else
                    {!! trans('admin/update.index.up_to_date', ['version' => '<code>' . e($installedLabel) . '</code>']) !!}
                @endif
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-6 col-sm-3 text-center">
        <a href="{{ $version->getDiscord() }}"><button class="btn btn-warning" style="width:100%;"><i class="fa fa-fw fa-support"></i> @lang('admin/index.get_help') <small>@lang('admin/index.via_discord')</small></button></a>
    </div>
    <div class="col-xs-6 col-sm-3 text-center">
        <a href="https://pterodactyl.io"><button class="btn btn-primary" style="width:100%;"><i class="fa fa-fw fa-link"></i> @lang('admin/index.documentation')</button></a>
    </div>
    <div class="clearfix visible-xs-block">&nbsp;</div>
    <div class="col-xs-6 col-sm-3 text-center">
        <a href="https://github.com/pterodactyl/panel"><button class="btn btn-primary" style="width:100%;"><i class="fa fa-fw fa-support"></i> @lang('admin/index.github')</button></a>
    </div>
    <div class="col-xs-6 col-sm-3 text-center">
        <a href="{{ $version->getDonations() }}"><button class="btn btn-success" style="width:100%;"><i class="fa fa-fw fa-money"></i> @lang('admin/index.support_project')</button></a>
    </div>
</div>
@endsection
