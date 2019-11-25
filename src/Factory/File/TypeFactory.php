<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\File;

use GibsonOS\Core\Factory\AbstractSingletonFactory;
use GibsonOS\Core\Service\File\TypeService;

class TypeFactory extends AbstractSingletonFactory
{
    protected static function createInstance(): TypeService
    {
        return new TypeService();
    }

    public static function create(): TypeService
    {
        /** @var TypeService $service */
        $service = parent::create();

        return $service;
    }
}
