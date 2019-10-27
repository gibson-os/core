<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\Image;

use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Service\Image;
use GibsonOS\Core\Service\Image\Thumbnail as ThumbnailService;

/**
 * @deprecated
 *
 * @package GibsonOS\Core\Factory\Image
 */
class Thumbnail
{
    /**
     * @param string $filename
     *
     * @throws FileNotFound
     * @throws SetError
     *
     * @return ThumbnailService
     */
    public static function createByFilename(string $filename): ThumbnailService
    {
        return new ThumbnailService(Manipulate::createByFilename($filename));
    }

    /**
     * @param Image $image
     *
     * @return ThumbnailService
     */
    public static function createByImage(Image $image): ThumbnailService
    {
        return new ThumbnailService(Manipulate::createByImage($image));
    }
}
