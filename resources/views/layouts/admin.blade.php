<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>{{ config('app.name', 'Pterodactyl') }} - @yield('title')</title>
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <meta name="_token" content="{{ csrf_token() }}">

        @include('partials.branding')

        @include('layouts.scripts')

        @section('scripts')
            {!! Theme::css('vendor/select2/select2.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/bootstrap/bootstrap.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/adminlte/admin.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/adminlte/colors/skin-blue.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/sweetalert/sweetalert.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/animate/animate.min.css?t={cache-version}') !!}
            {!! Theme::css('css/pterodactyl.css?t={cache-version}') !!}
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
            @if(\Pterodactyl\Services\Branding\BrandingService::accent() !== \Pterodactyl\Services\Branding\BrandingService::DEFAULT_ACCENT)
                <style>
                    /* Accent colour from Settings -> Design. */
                    .skin-blue .main-header .navbar, .skin-blue .main-header .navbar .sidebar-toggle:hover { background-color: var(--rp-accent); }
                    .skin-blue .main-header .logo, .skin-blue .main-header .logo:hover { background-color: var(--rp-accent-dark); }
                    .skin-blue .sidebar-menu > li.active > a, .skin-blue .sidebar-menu > li:hover > a { border-left-color: var(--rp-accent); }
                    .btn-primary, .btn-primary:hover, .btn-primary:focus, .btn-primary:active, .label-primary, .bg-light-blue { background-color: var(--rp-accent) !important; border-color: var(--rp-accent-dark) !important; }
                    .box.box-primary { border-top-color: var(--rp-accent); }
                    .nav-tabs-custom > .nav-tabs > li.active { border-top-color: var(--rp-accent); }
                    a, a:hover, a:focus { color: rgb(var(--rp-primary-400)); }
                    .btn a, .sidebar-menu a, .main-header a, .nav-tabs a, .pagination a { color: inherit; }
                </style>
            @endif

            <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
            <![endif]-->
        @show
    </head>
    <body class="hold-transition skin-blue fixed sidebar-mini">
        <div class="wrapper">
            <header class="main-header">
                <a href="{{ route('index') }}" class="logo">
                    @if($brandLogo = \Pterodactyl\Services\Branding\BrandingService::logoUrl())
                        <img src="{{ $brandLogo }}" alt="{{ config('app.name', 'Pterodactyl') }}" style="max-height:36px;max-width:190px;vertical-align:middle;">
                    @else
                        <span>{{ config('app.name', 'Pterodactyl') }}</span>
                    @endif
                </a>
                <nav class="navbar navbar-static-top">
                    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                        <span class="sr-only">@lang('admin/layout.header.toggle_navigation')</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            @if(Auth::user()->effectiveRole() === 'owner' && app(\Pterodactyl\Services\Update\UpdateService::class)->hasUpdate())
                                <li><a href="{{ route('admin.settings.updates') }}" class="text-yellow"><i class="fa fa-arrow-circle-up"></i> <span class="hidden-xs">@lang('admin/update.badge_long')</span></a></li>
                            @endif
                            <li class="user-menu">
                                <a href="{{ route('account') }}">
                                    <img src="https://www.gravatar.com/avatar/{{ md5(strtolower(Auth::user()->email)) }}?s=160" class="user-image" alt="User Image">
                                    <span class="hidden-xs">{{ Auth::user()->name_first }} {{ Auth::user()->name_last }}</span>
                                </a>
                            </li>
                            <li>
                                <li><a href="{{ route('index') }}" data-toggle="tooltip" data-placement="bottom" title="@lang('admin/layout.header.exit_admin_control')"><i class="fa fa-server"></i></a></li>
                            </li>
                            <li>
                                <li><a href="{{ route('auth.logout') }}" id="logoutButton" data-toggle="tooltip" data-placement="bottom" title="@lang('admin/layout.header.logout')"><i class="fa fa-sign-out"></i></a></li>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
            <aside class="main-sidebar">
                <section class="sidebar">
                    <ul class="sidebar-menu">
                        <li class="header">@lang('admin/layout.nav.basic_administration')</li>
@if(Auth::user()->root_admin)
                        <li class="{{ Route::currentRouteName() !== 'admin.index' ?: 'active' }}">
                            <a href="{{ route('admin.index') }}">
                                <i class="fa fa-home"></i> <span>@lang('admin/layout.nav.overview')</span>
                            </a>
                        </li>
@if(Auth::user()->isOwner())
                        <li class="{{ (! starts_with(Route::currentRouteName(), 'admin.settings') || Route::currentRouteName() === 'admin.settings.coins') ? '' : 'active' }}">
                            <a href="{{ route('admin.settings')}}">
                                <i class="fa fa-wrench"></i> <span>@lang('admin/layout.nav.settings')</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.api') ?: 'active' }}">
                            <a href="{{ route('admin.api.index')}}">
                                <i class="fa fa-gamepad"></i> <span>@lang('admin/layout.nav.application_api')</span>
                            </a>
                        </li>
@endif
@endif
@if(Auth::user()->root_admin)
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.audit') ?: 'active' }}">
                            <a href="{{ route('admin.audit') }}">
                                <i class="fa fa-history"></i> <span>@lang('admin/layout.nav.audit')</span>
                            </a>
                        </li>
@if(Auth::user()->isOwner())
                        <li class="{{ Route::currentRouteName() !== 'admin.settings.login' ?: 'active' }}">
                            <a href="{{ route('admin.settings.login') }}">
                                <i class="fa fa-comments"></i> <span>@lang('admin/layout.nav.discord_login')</span>
                            </a>
                        </li>
@endif
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.maintenance') ?: 'active' }}">
                            <a href="{{ route('admin.maintenance') }}">
                                <i class="fa fa-wrench"></i> <span>@lang('admin/layout.nav.maintenance')</span>
                                @if(in_array(config('mcpanel.maintenance.mode'), ['banner', 'lock'], true))
                                    <span class="pull-right-container"><small class="label pull-right {{ config('mcpanel.maintenance.mode') === 'lock' ? 'bg-red' : 'bg-yellow' }}">@lang('admin/maintenance.badge')</small></span>
                                @endif
                            </a>
                        </li>
@endif
@if(Auth::user()->hasStaffPermission('announcements'))
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.announcements') ?: 'active' }}">
                            <a href="{{ route('admin.announcements')}}">
                                <i class="fa fa-bullhorn"></i> <span>@lang('admin/layout.nav.announcements')</span>
                            </a>
                        </li>
@endif
@if(Auth::user()->hasStaffPermission('tickets'))
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.tickets') ?: 'active' }}">
                            <a href="{{ route('admin.tickets') }}">
                                <i class="fa fa-life-ring"></i> <span>@lang('admin/layout.nav.tickets')</span>
                                @php $openTickets = \Pterodactyl\Models\Ticket::query()->whereIn('status', ['open', 'customer_reply'])->count(); @endphp
                                @if($openTickets > 0)<span class="pull-right-container"><small class="label pull-right bg-red">{{ $openTickets }}</small></span>@endif
                            </a>
                        </li>
@endif
@if(Auth::user()->hasStaffPermission('users.view') || Auth::user()->hasStaffPermission('servers.view') || Auth::user()->root_admin)
                        <li class="header">@lang('admin/layout.nav.management')</li>
@if(Auth::user()->root_admin)
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.databases') ?: 'active' }}">
                            <a href="{{ route('admin.databases') }}">
                                <i class="fa fa-database"></i> <span>@lang('admin/layout.nav.databases')</span>
                            </a>
                        </li>
@if(Auth::user()->isOwner())
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.locations') ?: 'active' }}">
                            <a href="{{ route('admin.locations') }}">
                                <i class="fa fa-globe"></i> <span>@lang('admin/layout.nav.locations')</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.nodes') ?: 'active' }}">
                            <a href="{{ route('admin.nodes') }}">
                                <i class="fa fa-sitemap"></i> <span>@lang('admin/layout.nav.nodes')</span>
                            </a>
                        </li>
@endif
@endif
@if(Auth::user()->hasStaffPermission('servers.view'))
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.servers') ?: 'active' }}">
                            <a href="{{ route('admin.servers') }}">
                                <i class="fa fa-server"></i> <span>@lang('admin/layout.nav.servers')</span>
                            </a>
                        </li>
@endif
@if(Auth::user()->hasStaffPermission('users.view'))
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.users') ?: 'active' }}">
                            <a href="{{ route('admin.users') }}">
                                <i class="fa fa-users"></i> <span>@lang('admin/layout.nav.users')</span>
                            </a>
                        </li>
@endif
@endif
@if(Auth::user()->root_admin)
                        <li class="header">@lang('admin/layout.nav.coins_section')</li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.vouchers') ?: 'active' }}">
                            <a href="{{ route('admin.vouchers') }}">
                                <i class="fa fa-ticket"></i> <span>@lang('admin/layout.nav.vouchers')</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.plans') ?: 'active' }}">
                            <a href="{{ route('admin.plans') }}">
                                <i class="fa fa-cubes"></i> <span>@lang('admin/layout.nav.plans')</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.settings.coins') ?: 'active' }}">
                            <a href="{{ route('admin.settings.coins') }}">
                                <i class="fa fa-money"></i> <span>@lang('admin/layout.nav.coins_settings')</span>
                            </a>
                        </li>
@endif
@if(Auth::user()->isOwner())
                        <li class="header">@lang('admin/layout.nav.service_management')</li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.mounts') ?: 'active' }}">
                            <a href="{{ route('admin.mounts') }}">
                                <i class="fa fa-magic"></i> <span>@lang('admin/layout.nav.mounts')</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.nests') ?: 'active' }}">
                            <a href="{{ route('admin.nests') }}">
                                <i class="fa fa-th-large"></i> <span>@lang('admin/layout.nav.nests')</span>
                            </a>
                        </li>
@endif
                    </ul>
                </section>
            </aside>
            <div class="content-wrapper">
                <section class="content-header">
                    @yield('content-header')
                </section>
                <section class="content">
                    <div class="row">
                        <div class="col-xs-12">
                            @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    @lang('admin/layout.validation_error')<br><br>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @foreach (Alert::getMessages() as $type => $messages)
                                @foreach ($messages as $message)
                                    <div class="alert alert-{{ $type }} alert-dismissable" role="alert">
                                        {{ $message }}
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                    @yield('content')
                </section>
            </div>
            <footer class="main-footer">
                <div class="pull-right small text-gray" style="margin-right:10px;margin-top:-7px;">
                    <strong><i class="fa fa-fw {{ $appIsGit ? 'fa-git-square' : 'fa-code-fork' }}"></i></strong> {{ $appVersion }}<br />
                    <strong><i class="fa fa-fw fa-clock-o"></i></strong> {{ round(microtime(true) - LARAVEL_START, 3) }}s
                </div>
                @lang('admin/layout.footer.copyright') &copy; 2015 - {{ date('Y') }} <a href="https://pterodactyl.io/">Pterodactyl Software</a>.
            </footer>
        </div>
        @section('footer-scripts')
            <script src="/js/keyboard.polyfill.js" type="application/javascript"></script>
            <script>keyboardeventKeyPolyfill.polyfill();</script>

            {!! Theme::js('vendor/jquery/jquery.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/sweetalert/sweetalert.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/bootstrap/bootstrap.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/slimscroll/jquery.slimscroll.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/adminlte/app.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/bootstrap-notify/bootstrap-notify.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/select2/select2.full.min.js?t={cache-version}') !!}
            {!! Theme::js('js/admin/functions.js?t={cache-version}') !!}
            <script src="/js/autocomplete.js" type="application/javascript"></script>

            @if(Auth::user()->isStaff())
                <script>
                    $('#logoutButton').on('click', function (event) {
                        event.preventDefault();

                        var that = this;
                        swal({
                            title: '{{ trans('admin/layout.swal.logout_title') }}',
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d9534f',
                            cancelButtonColor: '#d33',
                            confirmButtonText: '{{ trans('admin/layout.swal.logout_confirm') }}'
                        }, function () {
                             $.ajax({
                                type: 'POST',
                                url: '{{ route('auth.logout') }}',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },complete: function () {
                                    window.location.href = '{{route('auth.login')}}';
                                }
                        });
                    });
                });
                </script>
            @endif

            <script>
                $(function () {
                    $('[data-toggle="tooltip"]').tooltip();
                })
            </script>
        @show
    </body>
</html>
