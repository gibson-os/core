<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\Flock;

class FlockFactory
{
    /**
     * @return Flock
     */
    public static function create(): Flock
    {
        /** @var Flock $flock */
        $flock = Flock::getInstance();

        return $flock;
    }
}
