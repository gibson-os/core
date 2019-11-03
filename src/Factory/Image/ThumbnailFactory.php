<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\Image;

use GibsonOS\Core\Factory\FileFactory;
use GibsonOS\Core\Service\Image\ThumbnailService;

/**
 * @deprecated
 *
 * @package GibsonOS\Core\Factory\Image
 */
class ThumbnailFactory
{
    /**
     * @return ThumbnailService
     */
    public static function create(): ThumbnailService
    {
        return new ThumbnailService(FileFactory::create());
    }
}
