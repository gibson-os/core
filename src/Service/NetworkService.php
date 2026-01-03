<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Event\NetworkEvent;
use GibsonOS\Core\Exception\NetworkException;
use Socket;

class NetworkService
{
    public function __construct(private readonly EventService $eventService)
    {
    }

    public function ping(string $host, int $timeout = 1): bool
    {
        $this->eventService->fire(
            NetworkEvent::class,
            NetworkEvent::TRIGGER_BEFORE_PING,
            [
                'host' => $host,
                'timeout' => $timeout,
            ],
        );

        $package = "\x08\x00\x7d\x4b\x00\x00\x00\x00PingHost";
        $socket = socket_create(AF_INET, SOCK_RAW, 1);

        if (!$socket instanceof Socket) {
            throw new NetworkException('Socket konnte nicht erstellt werden!');
        }

        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $timeout, 'usec' => 0]);
        socket_connect($socket, $host, 0);
        socket_send($socket, $package, strlen($package), 0);

        $result = socket_read($socket, 255) !== false;
        socket_close($socket);

        $this->eventService->fire(
            NetworkEvent::class,
            NetworkEvent::TRIGGER_AFTER_PING,
            [
                'host' => $host,
                'timeout' => $timeout,
                'result' => $result,
            ],
        );

        return $result;
    }
}
