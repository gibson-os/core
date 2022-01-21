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

            try {
                $timezone = $env->getString('timezone');
            } catch (GetError) {
                $timezone = null;
            }

            try {
                $latitude = $env->getFloat('date_latitude');
            } catch (GetError) {
                $latitude = null;
            }

            try {
                $longitude = $env->getFloat('date_longitude');
            } catch (GetError) {
                $longitude = null;
            }

            self::$instance = new DateTimeService($timezone, $latitude, $longitude);
        }

        return self::$instance;
    }
}
