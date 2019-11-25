<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\Image;

use GibsonOS\Core\Factory\AbstractSingletonFactory;
use GibsonOS\Core\Factory\FileFactory;
use GibsonOS\Core\Service\Image\DrawService;

class DrawFactory extends AbstractSingletonFactory
{
    protected static function createInstance(): DrawService
    {
        return new DrawService(FileFactory::create());
    }

    public static function create(): DrawService
    {
        /** @var DrawService $service */
        $service = parent::create();

        return $service;
    }
}
