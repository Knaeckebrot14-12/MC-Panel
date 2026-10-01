@php
    $brandFavicon = \Pterodactyl\Services\Branding\BrandingService::faviconUrl();
    $brandAccent = \Pterodactyl\Services\Branding\BrandingService::accentPalette();
@endphp
@if($brandFavicon)
    <link rel="icon" href="{{ $brandFavicon }}">
    <link rel="apple-touch-icon" href="{{ $brandFavicon }}">
@else
    <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" href="/favicons/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/favicons/favicon-16x16.png" sizes="16x16">
    <link rel="shortcut icon" href="/favicons/favicon.ico">
@endif
<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" content="rgb({{ str_replace(' ', ',', $brandAccent[700]) }})">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Recoded Ptero') }}">
<style>
    :root {
        @foreach($brandAccent as $shade => $rgb)
        --rp-primary-{{ $shade }}: {{ $rgb }};
        @endforeach
        --rp-accent: rgb({{ str_replace(' ', ',', $brandAccent[600]) }});
        --rp-accent-dark: rgb({{ str_replace(' ', ',', $brandAccent[800]) }});
    }
</style>
