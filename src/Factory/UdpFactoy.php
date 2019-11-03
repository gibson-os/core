<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Service\Udp;

class UdpFactoy
{
    /**
     * @param string $ip
     * @param int    $port
     *
     * @throws CreateError
     * @throws SetError
     *
     * @return Udp
     */
    public static function create(string $ip, int $port): Udp
    {
        return new Udp($ip, $port);
    }
}
