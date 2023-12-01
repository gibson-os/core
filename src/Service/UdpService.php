<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Dto\UdpMessage;
use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\Server\ReceiveError;
use GibsonOS\Core\Exception\Server\SendError;
use GibsonOS\Core\Exception\SetError;
use Psr\Log\LoggerInterface;
use Socket;

class UdpService
{
    private const MAX_CREATE_RETRY = 100;

    private const CREATE_RETRY_SLEEP_MS = 10000;

    private readonly Socket $socket;

    /**
     * @throws CreateError
     * @throws SetError
     */
    public function __construct(
        private readonly TracerService $tracerService,
        private readonly LoggerInterface $logger,
        string $ip,
        int $port,
    ) {
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        if (!$socket instanceof Socket) {
            throw new CreateError('Socket konnte nicht angelegt werden!');
        }

        $this->socket = $socket;
        $this->setTimeout();
        $this->socketBind($ip, $port);
    }

    /**
     * @throws SetError
     */
    public function setTimeout(int $timeout = 10): void
    {
        $this->tracerService->startSpan('udp set timeout', ['timeout' => $timeout]);
        $this->logger->debug(sprintf('Set UDP timeout %d s', $timeout));

        if (!socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $timeout, 'usec' => null])) {
            $exception = new SetError('Receive timeout konnte nicht gesetzt werden!');
            $this->tracerService->stopSpan($exception);

            throw $exception;
        }

        if (!socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => $timeout, 'usec' => null])) {
            $exception = new SetError('Send timeout konnte nicht gesetzt werden!');
            $this->tracerService->stopSpan($exception);

            throw $exception;
        }

        $this->tracerService->stopSpan();
    }

    /**
     * @throws SendError
     */
    public function send(UdpMessage $message): void
    {
        $this->tracerService->startSpan(
            'udp send',
            [
                'message' => $message->getMessage(),
                'ip' => $message->getIp(),
                'port' => $message->getPort(),
            ],
        );
        $this->logger->debug(sprintf(
            'Send UDP message "%s" to %s:%d',
            $message->getMessage(),
            $message->getIp(),
            $message->getPort(),
        ));

        $sendReturn = socket_sendto(
            $this->socket,
            $message->getMessage(),
            strlen($message->getMessage()),
            0,
            $message->getIp(),
            $message->getPort(),
        );

        if ($sendReturn === -1) {
            $exception = new SendError();
            $this->tracerService->stopSpan($exception);

            throw $exception;
        }

        $this->tracerService->stopSpan();
    }

    /**
     * @throws ReceiveError
     */
    public function receive(int $length, int $flags = 0): UdpMessage
    {
        $this->tracerService->startSpan('udp receive', [
            'length' => $length,
        ]);
        $this->logger->debug(sprintf('UDP receive message with length of %d', $length));

        if (socket_recvfrom($this->socket, $buf, $length, $flags, $ip, $port) === false) {
            $exception = new ReceiveError();
            $this->tracerService->stopSpan($exception);

            throw $exception;
        }

        $this->logger->info(sprintf('UDP received message "%s" from %s:%d', $buf, $ip, $port));
        $this->tracerService->setCustomParameters([
            'buffer' => $buf,
            'ip' => 'ip',
            'port' => 'port',
        ]);
        $this->tracerService->stopSpan();

        return new UdpMessage($ip, $port, $buf);
    }

    public function close(): void
    {
        $this->tracerService->startSpan('udp close', []);
        $this->logger->debug('Close UDP socket');
        socket_close($this->socket);
        $this->tracerService->stopSpan();
    }

    private function socketBind(string $ip, int $port, int $retry = 0): void
    {
        $this->tracerService->startSpan(
            'udp bind socker',
            [
                'ip' => $ip,
                'port' => $port,
                'retry' => $retry,
            ],
        );

        if (socket_bind($this->socket, $ip, $port)) {
            $this->tracerService->stopSpan();

            return;
        }

        if ($retry === self::MAX_CREATE_RETRY) {
            $exception = new CreateError(sprintf('Socket not bound on %s:%d!', $ip, $port));
            $this->tracerService->stopSpan($exception);

            throw $exception;
        }

        usleep(self::CREATE_RETRY_SLEEP_MS);
        $this->socketBind($ip, $port, ++$retry);
        $this->tracerService->stopSpan();
    }
}
