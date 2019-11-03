<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\ImageService;

class ImageFactory
{
    /**
     * @return ImageService
     */
    public static function create(): ImageService
    {
        return new ImageService(FileFactory::create());
    }
}
