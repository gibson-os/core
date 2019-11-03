<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Service\UdpService;

class UdpFactoy
{
    /**
     * @param string $ip
     * @param int    $port
     *
     * @throws SetError
     * @throws CreateError
     *
     * @return UdpService
     */
    public static function create(string $ip, int $port): UdpService
    {
        return new UdpService($ip, $port);
    }
}
