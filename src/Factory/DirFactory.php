<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\DirService;

class DirFactory extends AbstractSingletonFactory
{
    protected static function createInstance(): DirService
    {
        return new DirService();
    }

    public static function create(): DirService
    {
        /** @var DirService $service */
        $service = parent::create();

        return $service;
    }
}
