<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\Server\ReceiveError;
use GibsonOS\Core\Exception\Server\SendError;
use GibsonOS\Core\Exception\SetError;

class UdpService extends AbstractService
{
    /**
     * @var resource
     */
    private $socket;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var int
     */
    private $port = 0;

    /**
     * @var string
     */
    private $sendIp;

    /**
     * @var int
     */
    private $sendPort = 0;

    /**
     * @throws CreateError
     * @throws SetError
     */
    public function __construct(string $ip, int $port)
    {
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        if (!is_resource($socket)) {
            throw new CreateError('Socket konnte nicht angelegt werden!');
        }

        $this->socket = $socket;
        $this->setTimeout();

        $this->ip = $ip;
        $this->sendIp = $ip;
        $this->port = $port;
        $this->sendPort = $port;

        if (!socket_bind($this->socket, $ip, $port)) {
            throw new CreateError('Socket konnte nicht an ' . $ip . ':' . $port . ' gebunden werden!');
        }
    }

    /**
     * @throws SetError
     */
    public function setTimeout(int $timeout = 10): void
    {
        if (!socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $timeout, 'usec' => null])) {
            throw new SetError('Receive timeout konnte nicht gesetzt werden!');
        }

        if (!socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => $timeout, 'usec' => null])) {
            throw new SetError('Send timeout konnte nicht gesetzt werden!');
        }
    }

    /**
     * @throws SendError
     */
    public function send(string $msg, string $ip, int $port = 0): void
    {
        $this->sendIp = $ip;
        $this->sendPort = $port;

        if (socket_sendto($this->socket, $msg, strlen($msg), 0, $ip, $port) === -1) {
            throw new SendError();
        }
    }

    /**
     * @throws ReceiveError
     */
    public function receive(int $length, string $ip = null, int $port = null, int $flags = 0): string
    {
        if ($ip === null) {
            $ip = $this->sendIp;
        }

        if ($port === null) {
            $port = $this->sendPort;
        }

        if (socket_recvfrom($this->socket, $buf, $length, $flags, $ip, $port) === false) {
            throw new ReceiveError();
        }

        return $buf;
    }

    public function close(): void
    {
        socket_close($this->socket);
    }
}
