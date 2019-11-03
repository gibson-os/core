<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use DateTimeZone;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Service\DateTime;

class DateTimeFactory
{
    /**
     * @throws GetError
     *
     * @return DateTime
     */
    public static function create(): DateTime
    {
        $env = EnvFactory::create();

        return new DateTime(new DateTimeZone($env->getString('timezone')));
    }
}
