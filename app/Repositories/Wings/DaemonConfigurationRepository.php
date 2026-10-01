<?php

namespace Pterodactyl\Repositories\Wings;

use Pterodactyl\Models\Node;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\TransferException;
use Pterodactyl\Exceptions\Http\Connection\DaemonConnectionException;

/**
 * @method \Pterodactyl\Repositories\Wings\DaemonConfigurationRepository setNode(\Pterodactyl\Models\Node $node)
 * @method \Pterodactyl\Repositories\Wings\DaemonConfigurationRepository setServer(\Pterodactyl\Models\Server $server)
 */
class DaemonConfigurationRepository extends DaemonRepository
{
    /**
     * Returns system information from the wings instance.
     *
     * @throws DaemonConnectionException
     */
    public function getSystemInformation(?int $version = null): array
    {
        try {
            $response = $this->getHttpClient()->get('/api/system' . (!is_null($version) ? '?v=' . $version : ''));
        } catch (TransferException $exception) {
            throw new DaemonConnectionException($exception);
        }

        return json_decode($response->getBody()->__toString(), true);
    }

    /**
     * CPU, memory, disk, load and uptime of the machine (Recoded Ptero Wings only; older
     * Wings answer 404).
     *
     * @throws DaemonConnectionException
     */
    public function getUtilization(): array
    {
        try {
            $response = $this->getHttpClient()->get('/api/system/utilization', ['timeout' => 8]);
        } catch (TransferException $exception) {
            throw new DaemonConnectionException($exception);
        }

        return json_decode($response->getBody()->__toString(), true) ?? [];
    }

    /**
     * Tells Wings to install another version from the Recoded Ptero releases and restart.
     * Wings verifies the download against the release's checksums itself.
     *
     * @throws DaemonConnectionException
     */
    public function selfUpdate(string $version): array
    {
        try {
            $response = $this->getHttpClient()->post('/api/system/self-update', [
                'timeout' => 300,
                'json' => ['version' => $version],
            ]);
        } catch (TransferException $exception) {
            throw new DaemonConnectionException($exception);
        }

        return json_decode($response->getBody()->__toString(), true) ?? [];
    }

    /**
     * Updates the configuration information for a daemon. Updates the information for
     * this instance using a passed-in model. This allows us to change plenty of information
     * in the model, and still use the old, pre-update model to actually make the HTTP request.
     *
     * @throws DaemonConnectionException
     */
    public function update(Node $node): ResponseInterface
    {
        try {
            return $this->getHttpClient()->post(
                '/api/update',
                ['json' => $node->getConfiguration()]
            );
        } catch (TransferException $exception) {
            throw new DaemonConnectionException($exception);
        }
    }
}
