@include('partials/admin.settings.notice')

@section('settings::nav')
    @yield('settings::notice')
    <div class="row">
        <div class="col-xs-12">
            <div class="nav-tabs-custom nav-tabs-floating">
                <ul class="nav nav-tabs">
                    <li @if($activeTab === 'basic')class="active"@endif><a href="{{ route('admin.settings') }}">@lang('admin/settings.nav.general')</a></li>
                    <li @if($activeTab === 'mail')class="active"@endif><a href="{{ route('admin.settings.mail') }}">@lang('admin/settings.nav.mail')</a></li>
                    <li @if($activeTab === 'advanced')class="active"@endif><a href="{{ route('admin.settings.advanced') }}">@lang('admin/settings.nav.advanced')</a></li>
                    <li @if($activeTab === 'login')class="active"@endif><a href="{{ route('admin.settings.login') }}">@lang('admin/settings.nav.login')</a></li>
                    <li @if($activeTab === 'roles')class="active"@endif><a href="{{ route('admin.settings.roles') }}">@lang('admin/settings.nav.roles')</a></li>
                    <li @if($activeTab === 'design')class="active"@endif><a href="{{ route('admin.settings.design') }}">@lang('admin/settings.nav.design')</a></li>
                    <li @if($activeTab === 'monitoring')class="active"@endif><a href="{{ route('admin.settings.monitoring') }}">@lang('admin/settings.nav.monitoring')</a></li>
                    <li @if($activeTab === 'abuse')class="active"@endif><a href="{{ route('admin.settings.abuse') }}">@lang('admin/settings.nav.abuse')</a></li>
                    <li @if($activeTab === 'iplockout')class="active"@endif><a href="{{ route('admin.settings.iplockout') }}">@lang('admin/settings.nav.iplockout')</a></li>
                    <li @if($activeTab === 'subdomains')class="active"@endif><a href="{{ route('admin.settings.subdomains') }}">@lang('admin/settings.nav.subdomains')</a></li>
                    <li @if($activeTab === 'updates')class="active"@endif><a href="{{ route('admin.settings.updates') }}">@lang('admin/settings.nav.updates') @if(app(\Pterodactyl\Services\Update\UpdateService::class)->hasUpdate())<span class="label label-warning">@lang('admin/update.badge')</span>@endif</a></li>
                </ul>
            </div>
        </div>
    </div>
@endsection
