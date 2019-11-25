<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Service\UdpService;

class UdpFactoy
{
    /**
     * @throws SetError
     * @throws CreateError
     */
    public static function create(string $ip, int $port): UdpService
    {
        return new UdpService($ip, $port);
    }
}
