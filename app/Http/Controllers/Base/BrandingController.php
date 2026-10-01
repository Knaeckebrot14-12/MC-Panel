<?php

namespace Pterodactyl\Http\Controllers\Base;

use Illuminate\Http\JsonResponse;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Branding\BrandingService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BrandingController extends Controller
{
    /**
     * Serves an uploaded logo, favicon or background. Names are random per upload, so they can be
     * cached for a long time.
     */
    public function file(string $file): BinaryFileResponse
    {
        abort_unless(preg_match('/^(logo|favicon|background)-[0-9a-f]{12}\.(png|jpg|webp|ico)$/', $file), 404);
        $path = BrandingService::directory() . '/' . $file;
        abort_unless(is_file($path), 404);

        $type = match (pathinfo($file, PATHINFO_EXTENSION)) {
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/x-icon',
        };

        return response()->file($path, [
            'Content-Type' => $type,
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Web app manifest, so the panel can be installed as an app (phone home screen, desktop).
     */
    public function manifest(): JsonResponse
    {
        $favicon = BrandingService::faviconUrl();
        $icons = $favicon && !str_ends_with($favicon, '.ico')
            ? [
                ['src' => $favicon, 'sizes' => 'any', 'type' => str_ends_with($favicon, '.png') ? 'image/png' : (str_ends_with($favicon, '.webp') ? 'image/webp' : 'image/jpeg'), 'purpose' => 'any'],
            ]
            : [
                ['src' => '/favicons/android-chrome-192x192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/favicons/android-chrome-512x512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
            ];

        $dark = BrandingService::defaultTheme() === 'dark';

        return new JsonResponse([
            'name' => config('app.name', 'Recoded Ptero'),
            'short_name' => mb_substr(config('app.name', 'Recoded Ptero'), 0, 12),
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'background_color' => $dark ? '#1f2933' : '#f5f7fa',
            'theme_color' => 'rgb(' . str_replace(' ', ',', BrandingService::accentPalette()[700]) . ')',
            'icons' => $icons,
        ], 200, ['Content-Type' => 'application/manifest+json', 'Cache-Control' => 'public, max-age=3600']);
    }
}
