<?php
namespace GibsonOS\Core\Factory\Image;

use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Service\Image;
use GibsonOS\Core\Service\Image\Thumbnail as ThumbnailService;

/**
 * @deprecated
 * @package GibsonOS\Core\Factory\Image
 */
class Thumbnail
{
    /**
     * @param string $filename
     * @return ThumbnailService
     * @throws FileNotFound
     */
    static function createByFilename($filename)
    {
        return new ThumbnailService(Manipulate::createByFilename($filename));
    }

    /**
     * @param Image $image
     * @return ThumbnailService
     */
    static function createByImage(Image $image)
    {
        return new ThumbnailService(Manipulate::createByImage($image));
    }
}