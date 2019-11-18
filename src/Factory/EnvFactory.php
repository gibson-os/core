<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\EnvService;

class EnvFactory extends AbstractSingletonFactory
{
    protected static function createInstance(): EnvService
    {
        return new EnvService();
    }

    public static function create(): EnvService
    {
        /** @var EnvService $service */
        $service = parent::create();

        return $service;
    }
}
