<?php

namespace Pterodactyl\Services\Minecraft;

use Pterodactyl\Models\Server;

/**
 * Asks a Minecraft Java server for its status the same way the in-game server list does
 * (Server List Ping), which returns the online/max player counts and a sample of names.
 */
class ServerListPing
{
    /**
     * @return array{online: int, max: int, sample: array<int, array{name: string, id: string}>, version: string|null}|null
     */
    public function query(string $host, int $port, float $timeout = 1.5): ?array
    {
        $socket = @fsockopen($host, $port, $errno, $error, $timeout);
        if (!$socket) {
            return null;
        }

        try {
            stream_set_timeout($socket, (int) ceil($timeout));

            $handshake = "\x00" . $this->varInt(765) . $this->string($host) . pack('n', $port) . $this->varInt(1);
            fwrite($socket, $this->varInt(strlen($handshake)) . $handshake);
            fwrite($socket, "\x01\x00");

            $this->readVarInt($socket); // packet length
            if ($this->readVarInt($socket) !== 0x00) {
                return null;
            }

            $length = $this->readVarInt($socket);
            if ($length <= 0 || $length > 1048576) {
                return null;
            }

            $json = '';
            while (strlen($json) < $length) {
                $chunk = fread($socket, $length - strlen($json));
                if ($chunk === false || $chunk === '') {
                    return null;
                }
                $json .= $chunk;
            }

            $data = json_decode($json, true);
            if (!is_array($data) || !isset($data['players'])) {
                return null;
            }

            $sample = [];
            foreach ($data['players']['sample'] ?? [] as $player) {
                // Some plugins put decorative lines into the sample; real players have a 16 char max name.
                if (isset($player['name'], $player['id']) && preg_match('/^[A-Za-z0-9_.*]{1,17}$/', $player['name'])) {
                    $sample[] = ['name' => $player['name'], 'id' => $player['id']];
                }
            }

            return [
                'online' => (int) ($data['players']['online'] ?? 0),
                'max' => (int) ($data['players']['max'] ?? 0),
                'sample' => $sample,
                'version' => $data['version']['name'] ?? null,
            ];
        } catch (\Throwable) {
            return null;
        } finally {
            fclose($socket);
        }
    }

    /**
     * Pings the server's primary allocation. Wildcard/loopback allocations are reached through the node.
     */
    public function forServer(Server $server, float $timeout = 1.5): ?array
    {
        $allocation = $server->allocation;
        if (!$allocation) {
            return null;
        }

        $host = $allocation->ip;
        if (in_array($host, ['0.0.0.0', '::', '127.0.0.1', '::1'], true)) {
            $host = $server->node->fqdn;
        }

        return $this->query($host, (int) $allocation->port, $timeout);
    }

    private function varInt(int $value): string
    {
        $out = '';
        do {
            $byte = $value & 0x7F;
            $value >>= 7;
            if ($value !== 0) {
                $byte |= 0x80;
            }
            $out .= chr($byte);
        } while ($value !== 0);

        return $out;
    }

    private function string(string $value): string
    {
        return $this->varInt(strlen($value)) . $value;
    }

    /**
     * @param resource $socket
     */
    private function readVarInt($socket): int
    {
        $value = 0;
        for ($i = 0; $i < 5; ++$i) {
            $char = fgetc($socket);
            if ($char === false) {
                throw new \RuntimeException('Connection closed');
            }
            $byte = ord($char);
            $value |= ($byte & 0x7F) << (7 * $i);
            if (($byte & 0x80) === 0) {
                return $value;
            }
        }

        throw new \RuntimeException('VarInt too long');
    }
}
