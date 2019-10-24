<?php
namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\Server\ReceiveError;
use GibsonOS\Core\Exception\Server\SendError;
use GibsonOS\Core\Exception\SetError;

class Udp extends AbstractService
{
    /**
     * @var resource Socket
     */
    private $socket;
    /**
     * @var string IP
     */
    private $ip;
    /**
     * @var int Port
     */
    private $port = 0;
    /**
     * @var string Sender IP
     */
    private $sendIp;
    /**
     * @var int Sender Port
     */
    private $sendPort = 0;

    /**
     * @param string $ip
     * @param int $port
     * @throws CreateError
     * @throws SetError
     */
    public function __construct($ip, $port)
    {
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        $this->setTimeout();

        if (!is_resource($this->socket)) {
            throw new CreateError('Socket konnte nicht angelegt werden!');
        }

        $this->ip = $ip;
        $this->port = $port;

        if (!socket_bind($this->socket, $ip, $port)) {
            throw new CreateError('Socket konnte nicht an ' . $ip . ':' . $port . ' gebunden werden!');
        }
    }

    /**
     * Setzt Timeout
     *
     * Setzt das Timeout für Übertragungen.
     *
     * @param int $timeout Timeout
     * @throws SetError
     */
    public function setTimeout($timeout = 10)
    {
        if (!socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $timeout, 'usec' => null])) {
            throw new SetError('Receive timeout konnte nicht gesetzt werden!');
        }

        if (!socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => $timeout, 'usec' => null])) {
            throw new SetError('Send timeout konnte nicht gesetzt werden!');
        }
    }

    /**
     * Sendet Nachricht
     *
     * Sendet eine Nachricht.
     *
     * @param string $msg Nachricht
     * @param string $ip IP
     * @param int $port Port
     * @throws SendError
     */
    public function send($msg, $ip, $port = 0)
    {
        $this->sendIp = $ip;
        $this->sendPort = $port;

        if (socket_sendto($this->socket, $msg, strlen($msg), 0, $ip, $port) === -1) {
            throw new SendError();
        }
    }

    /**
     * Empfängt Nachricht
     *
     * Wird IP nicht übergeben nimmt er die aus der Initalisierung.<br>
     * Wird Port nicht übergeben nimmt er die aus der Initalisierung.
     *
     * @param int $len Länge
     * @param string|null $ip IP
     * @param int|null $port Port
     * @param int $flags Einstellungen
     * @return string
     * @throws ReceiveError
     */
    public function receive($len, $ip = null, $port = null, $flags = 0)
    {
        if (is_null($ip)) {
            $ip = $this->sendIp;
        }

        if (is_null($port)) {
            $port = $this->sendPort;
        }

        if (socket_recvfrom($this->socket, $buf, $len, $flags, $ip, $port) === false) {
            throw new ReceiveError();
        }

        return $buf;
    }

    /**
     * Schließt Socket
     *
     * Schließt eine Socket Verbindung.
     */
    public function close()
    {
        socket_close($this->socket);
    }
}