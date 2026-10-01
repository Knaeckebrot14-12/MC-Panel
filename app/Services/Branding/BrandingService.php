<?php

namespace Pterodactyl\Services\Branding;

use Illuminate\Http\UploadedFile;
use Pterodactyl\Exceptions\DisplayException;

/**
 * Logo, favicon, accent colour and background of the panel (Admin -> Settings -> Design).
 * Uploaded images are stored in /app/var/branding, which survives updates.
 */
class BrandingService
{
    public const TYPES = ['logo', 'favicon', 'background'];

    private const MAX_BYTES = 2 * 1024 * 1024;

    /** Default accent (Tailwind blue-500) and gray scale of the panel, as "r g b". */
    public const DEFAULT_ACCENT = '#3b82f6';

    public static function directory(): string
    {
        return base_path('var/branding');
    }

    /**
     * Stores an uploaded image and returns its file name. Only PNG, JPEG, WebP and ICO are accepted
     * (no SVG: it can carry scripts), checked by content, not by the name the browser sent.
     *
     * @throws DisplayException
     */
    public function store(string $type, UploadedFile $file): string
    {
        if (!in_array($type, self::TYPES, true) || !$file->isValid() || $file->getSize() > self::MAX_BYTES) {
            throw new DisplayException(trans('admin/design.errors.file'));
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath());
        $extension = match ($mime) {
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            'image/vnd.microsoft.icon', 'image/x-icon' => 'ico',
            default => null,
        };
        if (!$extension || ($extension === 'ico' && $type !== 'favicon') || ($extension !== 'ico' && @getimagesize($file->getRealPath()) === false)) {
            throw new DisplayException(trans('admin/design.errors.file'));
        }

        if (!is_dir(self::directory())) {
            mkdir(self::directory(), 0755, true);
        }

        // A new name for every upload, so browsers and the app don't keep showing a cached old image.
        $name = $type . '-' . substr(sha1_file($file->getRealPath()), 0, 12) . '.' . $extension;
        $file->move(self::directory(), $name);
        $this->cleanup($type, $name);

        return $name;
    }

    public function cleanup(string $type, ?string $keep = null): void
    {
        foreach (glob(self::directory() . '/' . $type . '-*') ?: [] as $path) {
            if (basename($path) !== $keep) {
                @unlink($path);
            }
        }
    }

    public static function url(?string $name): ?string
    {
        return $name && preg_match('/^(logo|favicon|background)-[0-9a-f]{12}\.(png|jpg|webp|ico)$/', $name) && is_file(self::directory() . '/' . $name)
            ? '/branding/' . $name
            : null;
    }

    public static function logoUrl(): ?string
    {
        return self::url(config('mcpanel.branding.logo'));
    }

    public static function faviconUrl(): ?string
    {
        return self::url(config('mcpanel.branding.favicon'));
    }

    public static function backgroundUrl(): ?string
    {
        return self::url(config('mcpanel.branding.background'));
    }

    public static function accent(): string
    {
        $accent = (string) config('mcpanel.branding.accent');

        return preg_match('/^#[0-9a-f]{6}$/i', $accent) ? strtolower($accent) : self::DEFAULT_ACCENT;
    }

    public static function defaultTheme(): string
    {
        return config('mcpanel.branding.default_theme') === 'light' ? 'light' : 'dark';
    }

    /**
     * CSS variables for the accent palette (50..900) built from one colour: the same hue and
     * saturation with Tailwind-like lightness steps. Values are "r g b" for rgb(var(--x) / a).
     */
    public static function accentPalette(): array
    {
        [$h, $s] = self::hsl(self::accent());
        $steps = [50 => 96, 100 => 92, 200 => 85, 300 => 75, 400 => 64, 500 => 54, 600 => 46, 700 => 38, 800 => 31, 900 => 25];

        // The default accent keeps Tailwind's own blue exactly.
        if (self::accent() === self::DEFAULT_ACCENT) {
            return [
                50 => '239 246 255', 100 => '219 234 254', 200 => '191 219 254', 300 => '147 197 253', 400 => '96 165 250',
                500 => '59 130 246', 600 => '37 99 235', 700 => '29 78 216', 800 => '30 64 175', 900 => '30 58 138',
            ];
        }

        $out = [];
        foreach ($steps as $shade => $l) {
            $out[$shade] = implode(' ', self::rgb($h, min($s, 95), $l));
        }

        return $out;
    }

    private static function hsl(string $hex): array
    {
        [$r, $g, $b] = array_map(fn ($c) => hexdec($c) / 255, str_split(ltrim($hex, '#'), 2));
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        $d = $max - $min;
        if ($d == 0) {
            return [0, 0, $l * 100];
        }
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
        $h = match ($max) {
            $r => (($g - $b) / $d + ($g < $b ? 6 : 0)),
            $g => (($b - $r) / $d + 2),
            default => (($r - $g) / $d + 4),
        } * 60;

        return [$h, $s * 100, $l * 100];
    }

    private static function rgb(float $h, float $s, float $l): array
    {
        $s /= 100;
        $l /= 100;
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;
        [$r, $g, $b] = match (true) {
            $h < 60 => [$c, $x, 0],
            $h < 120 => [$x, $c, 0],
            $h < 180 => [0, $c, $x],
            $h < 240 => [0, $x, $c],
            $h < 300 => [$x, 0, $c],
            default => [$c, 0, $x],
        };

        return [(int) round(($r + $m) * 255), (int) round(($g + $m) * 255), (int) round(($b + $m) * 255)];
    }
}
