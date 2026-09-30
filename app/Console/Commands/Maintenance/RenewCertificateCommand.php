<?php

namespace Pterodactyl\Console\Commands\Maintenance;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Keeps the panel's own Let's Encrypt certificate (inside this container) valid. Runs twice a day
 * from the scheduler; certbot only renews within 30 days of expiry, so most runs just check.
 * The result is kept for the admin overview.
 */
class RenewCertificateCommand extends Command
{
    public const CACHE_KEY = 'mcpanel:ssl';

    protected $description = 'Renews the panel\'s Let\'s Encrypt certificate when it is due and records its expiry date.';

    protected $signature = 'p:ssl:renew {--dry-run : Test the renewal against Let\'s Encrypt\'s staging server without changing anything}';

    public function handle(): int
    {
        $domain = parse_url((string) config('app.url'), PHP_URL_HOST);
        $cert = "/etc/letsencrypt/live/$domain/cert.pem";

        if (parse_url((string) config('app.url'), PHP_URL_SCHEME) !== 'https' || !$domain || !is_file($cert)) {
            // HTTP panels, or HTTPS handled by an external reverse proxy: nothing to renew here.
            Cache::forever(self::CACHE_KEY, ['managed' => false, 'checked_at' => now()->toIso8601String()]);
            $this->info('The panel does not manage its own certificate.');

            return self::SUCCESS;
        }

        $before = $this->expiry($cert);
        $command = sprintf(
            'certbot renew --cert-name %s --webroot -w /var/www/acme --non-interactive --quiet %s 2>&1',
            escapeshellarg($domain),
            $this->option('dry-run') ? '--dry-run' : '--deploy-hook "nginx -s reload"'
        );
        exec($command, $output, $code);

        if ($this->option('dry-run')) {
            $this->line(implode("\n", $output));
            $code === 0 ? $this->info('Automatic renewal works.') : $this->error('The renewal test failed.');

            return $code === 0 ? self::SUCCESS : self::FAILURE;
        }

        $after = $this->expiry($cert);
        $previous = Cache::get(self::CACHE_KEY, []);

        Cache::forever(self::CACHE_KEY, [
            'managed' => true,
            'domain' => $domain,
            'expires_at' => optional($after)->toIso8601String(),
            'renewed_at' => ($before && $after && $after->gt($before)) ? now()->toIso8601String() : ($previous['renewed_at'] ?? null),
            'error' => $code === 0 ? null : mb_substr(trim(implode("\n", $output)), -500),
            'checked_at' => now()->toIso8601String(),
        ]);

        if ($code !== 0) {
            $this->error('Renewal failed: ' . implode("\n", $output));

            return self::FAILURE;
        }

        $this->info("Certificate for $domain valid until " . optional($after)->toDateTimeString());

        return self::SUCCESS;
    }

    private function expiry(string $cert): ?CarbonImmutable
    {
        $info = @openssl_x509_parse((string) @file_get_contents($cert));

        return $info ? CarbonImmutable::createFromTimestamp($info['validTo_time_t']) : null;
    }
}
