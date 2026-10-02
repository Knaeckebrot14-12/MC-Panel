<?php

namespace Pterodactyl\Console\Commands\Server;

use Illuminate\Console\Command;
use Pterodactyl\Services\Abuse\AbuseScanService;

class AbuseScanCommand extends Command
{
    protected $description = 'Flags servers that look abused (sustained CPU, miner keywords, huge outbound traffic) for the team. Never acts on a server.';

    protected $signature = 'p:abuse:scan';

    public function __construct(private AbuseScanService $scanner)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (!AbuseScanService::enabled()) {
            $this->info('Abuse detection is turned off.');

            return self::SUCCESS;
        }

        $summary = $this->scanner->scan();
        $this->info(sprintf(
            'Checked %d running servers: %d new flag(s), %d refreshed.',
            $summary['checked'],
            $summary['new'],
            $summary['refreshed']
        ));
        foreach ($summary['flags'] as $flag) {
            $this->line('  new: ' . $flag);
        }

        return self::SUCCESS;
    }
}
