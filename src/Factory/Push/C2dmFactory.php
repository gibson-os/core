<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\Push;

use GibsonOS\Core\Factory\AbstractSingletonFactory;
use GibsonOS\Core\Service\Push\C2DmService;

class C2DmFactory extends AbstractSingletonFactory
{
    protected static function createInstance(): C2dmService
    {
        return new C2dmService();
    }

    public static function create(): C2DmService
    {
        /** @var C2DmService $service */
        $service = parent::create();

        return $service;
    }
}
