<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\EnvService;

class DateTimeFactory
{
    private static ?DateTimeService $instance = null;

    /**
     * @throws GetError
     */
    public static function get(): DateTimeService
    {
        if (self::$instance === null) {
            $env = new EnvService();

            self::$instance = new DateTimeService(
                $env->getString('timezone'),
                $env->getFloat('date_latitude'),
                $env->getFloat('date_longitude')
            );
        }

        return self::$instance;
    }
}
