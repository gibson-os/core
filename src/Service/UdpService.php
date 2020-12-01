<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Dto\UdpMessage;
use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\Server\ReceiveError;
use GibsonOS\Core\Exception\Server\SendError;
use GibsonOS\Core\Exception\SetError;
use Psr\Log\LoggerInterface;

class UdpService extends AbstractService
{
    /**
     * @var resource
     */
    private $socket;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @throws CreateError
     * @throws SetError
     */
    public function __construct(LoggerInterface $logger, string $ip, int $port)
    {
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        if (!is_resource($socket)) {
            throw new CreateError('Socket konnte nicht angelegt werden!');
        }

        $this->logger = $logger;
        $this->socket = $socket;
        $this->setTimeout();

        if (!socket_bind($this->socket, $ip, $port)) {
            throw new CreateError('Socket konnte nicht an ' . $ip . ':' . $port . ' gebunden werden!');
        }
    }

    /**
     * @throws SetError
     */
    public function setTimeout(int $timeout = 10): void
    {
        $this->logger->debug(sprintf('Set UDP timeout %d s', $timeout));

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
    public function send(UdpMessage $message): void
    {
        $this->logger->debug(sprintf(
            'Send UDP message "%s" to %s:%d',
            $message->getMessage(),
            $message->getIp(),
            $message->getPort()
        ));

        $sendReturn = socket_sendto(
            $this->socket,
            $message->getMessage(),
            strlen($message->getMessage()),
            0,
            $message->getIp(),
            $message->getPort()
        );

        if ($sendReturn === -1) {
            throw new SendError();
        }
    }

    /**
     * @throws ReceiveError
     */
    public function receive(int $length, int $flags = 0): UdpMessage
    {
        $this->logger->debug(sprintf('UDP receive message with length of %d', $length));

        if (socket_recvfrom($this->socket, $buf, $length, $flags, $ip, $port) === false) {
            throw new ReceiveError();
        }

        $this->logger->info(sprintf('UDP received message "%s" from %s:%d', $buf, $ip, $port));

        return new UdpMessage($ip, $port, $buf);
    }

    public function close(): void
    {
        $this->logger->debug('Close UDP socket');
        socket_close($this->socket);
    }
}
