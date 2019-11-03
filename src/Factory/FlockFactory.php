<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\FlockService;

class FlockFactory
{
    /**
     * @return FlockService
     */
    public static function create(): FlockService
    {
        /** @var FlockService $flock */
        $flock = FlockService::getInstance();

        return $flock;
    }
}
