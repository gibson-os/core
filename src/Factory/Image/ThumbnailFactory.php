<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\Image;

use GibsonOS\Core\Factory\AbstractSingletonFactory;
use GibsonOS\Core\Factory\FileFactory;
use GibsonOS\Core\Service\Image\ThumbnailService;

/**
 * @deprecated
 *
 * @package GibsonOS\Core\Factory\Image
 */
class ThumbnailFactory extends AbstractSingletonFactory
{
    /**
     * @return ThumbnailService
     */
    protected static function createInstance(): ThumbnailService
    {
        return new ThumbnailService(FileFactory::create());
    }

    public static function create(): ThumbnailService
    {
        /** @var ThumbnailService $service */
        $service = parent::create();

        return $service;
    }
}
