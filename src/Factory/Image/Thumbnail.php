<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\Image;

use GibsonOS\Core\Factory\File;
use GibsonOS\Core\Service\Image\Thumbnail as ThumbnailService;

/**
 * @deprecated
 *
 * @package GibsonOS\Core\Factory\Image
 */
class Thumbnail
{
    /**
     * @return ThumbnailService
     */
    public static function create(): ThumbnailService
    {
        return new ThumbnailService(File::create());
    }
}
