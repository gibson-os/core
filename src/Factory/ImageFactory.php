<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\ImageService;

class ImageFactory extends AbstractSingletonFactory
{
    /**
     * @return ImageService
     */
    protected static function createInstance(): ImageService
    {
        return new ImageService(FileFactory::create());
    }

    public static function create(): ImageService
    {
        /** @var ImageService $service */
        $service = parent::create();

        return $service;
    }
}
