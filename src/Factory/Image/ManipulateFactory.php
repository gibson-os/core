<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\Image;

use GibsonOS\Core\Factory\AbstractSingletonFactory;
use GibsonOS\Core\Factory\FileFactory;
use GibsonOS\Core\Service\Image\ManipulateService;

class ManipulateFactory extends AbstractSingletonFactory
{
    /**
     * @return ManipulateService
     */
    protected static function createInstance(): ManipulateService
    {
        return new ManipulateService(FileFactory::create());
    }

    public static function create(): ManipulateService
    {
        /** @var ManipulateService $service */
        $service = parent::create();

        return $service;
    }
}
