<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\Image;

use GibsonOS\Core\Factory\FileFactory;
use GibsonOS\Core\Service\Image\Thumbnail;

/**
 * @deprecated
 *
 * @package GibsonOS\Core\Factory\Image
 */
class ThumbnailFactory
{
    /**
     * @return Thumbnail
     */
    public static function create(): Thumbnail
    {
        return new Thumbnail(FileFactory::create());
    }
}
