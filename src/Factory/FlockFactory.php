<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\FlockService;

class FlockFactory extends AbstractSingletonFactory
{
    /**
     * @return FlockService
     */
    protected static function createInstance(): FlockService
    {
        return new FlockService();
    }

    public static function create(): FlockService
    {
        /** @var FlockService $service */
        $service = parent::create();

        return $service;
    }
}
