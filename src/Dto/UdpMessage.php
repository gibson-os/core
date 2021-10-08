<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto;

class UdpMessage
{
    public function __construct(private string $ip, private int $port, private string $message)
    {
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): UdpMessage
    {
        $this->ip = $ip;

        return $this;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): UdpMessage
    {
        $this->port = $port;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): UdpMessage
    {
        $this->message = $message;

        return $this;
    }
}
