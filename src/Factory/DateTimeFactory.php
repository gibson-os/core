<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use DateTimeZone;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Service\DateTimeService;

class DateTimeFactory extends AbstractSingletonFactory
{
    /**
     * @throws GetError
     *
     * @return DateTimeService
     */
    protected static function createInstance(): DateTimeService
    {
        $env = EnvFactory::create();

        return new DateTimeService(new DateTimeZone($env->getString('timezone')));
    }

    public static function create(): DateTimeService
    {
        /** @var DateTimeService $service */
        $service = parent::create();

        return $service;
    }
}
