<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use DateTimeZone;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Service\DateTimeService;

class DateTimeFactory
{
    /**
     * @throws GetError
     *
     * @return DateTimeService
     */
    public static function create(): DateTimeService
    {
        $env = EnvFactory::create();

        return new DateTimeService(new DateTimeZone($env->getString('timezone')));
    }
}
