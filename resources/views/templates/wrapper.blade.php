<!DOCTYPE html>
<html>
    <head>
        <title>{{ config('app.name', 'Pterodactyl') }}</title>

        @section('meta')
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
            <meta name="csrf-token" content="{{ csrf_token() }}">
            <meta name="robots" content="noindex">
            @include('partials.branding')
            <style>
                /* Gray scale of the panel; the light theme simply turns it around. */
                :root {
                    --rp-gray-50: 216 33% 97%; --rp-gray-100: 214 15% 91%; --rp-gray-200: 210 16% 82%;
                    --rp-gray-300: 211 13% 65%; --rp-gray-400: 211 10% 53%; --rp-gray-500: 211 12% 43%;
                    --rp-gray-600: 209 14% 37%; --rp-gray-700: 209 18% 30%; --rp-gray-800: 209 20% 25%;
                    --rp-gray-900: 210 24% 16%; --rp-black: 208 25% 10%;
                }
                html[data-theme="light"] {
                    --rp-gray-50: 210 24% 16%; --rp-gray-100: 209 20% 22%; --rp-gray-200: 209 18% 30%;
                    --rp-gray-300: 209 14% 37%; --rp-gray-400: 211 12% 43%; --rp-gray-500: 211 10% 60%;
                    --rp-gray-600: 213 18% 87%; --rp-gray-700: 0 0% 100%; --rp-gray-800: 210 20% 96%;
                    --rp-gray-900: 214 25% 92%; --rp-black: 216 33% 97%;
                    color-scheme: light;
                }
                @if(\Pterodactyl\Services\Branding\BrandingService::backgroundUrl())
                body { background-image: url('{{ \Pterodactyl\Services\Branding\BrandingService::backgroundUrl() }}'); background-size: cover; background-position: center; background-attachment: fixed; }
                @endif
            </style>
            <script>
                // Before anything renders, so the page never flashes in the wrong theme.
                (function () {
                    var theme = '{{ \Pterodactyl\Services\Branding\BrandingService::defaultTheme() }}';
                    try { theme = localStorage.getItem('rp-theme') || theme; } catch (e) {}
                    document.documentElement.setAttribute('data-theme', theme === 'light' ? 'light' : 'dark');
                })();
            </script>
        @show

        @section('user-data')
            @if(!is_null(Auth::user()))
                <script>
                    window.PterodactylUser = {!! json_encode(Auth::user()->toVueObject()) !!};
                </script>
            @endif
            @if(!empty($siteConfiguration))
                <script>
                    window.SiteConfiguration = {!! json_encode($siteConfiguration) !!};
                </script>
            @endif
        @show

        @yield('assets')

        @include('layouts.scripts')
    </head>
    <body class="{{ $css['body'] ?? 'bg-neutral-50' }}">
        @section('content')
            @yield('above-container')
            @yield('container')
            @yield('below-container')
        @show
        @section('scripts')
            {!! $asset->js('main.js') !!}
        @show
    </body>
</html>
