<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\Image;

use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Factory\Image;
use GibsonOS\Core\Service\Image as ImageService;
use GibsonOS\Core\Service\Image\Draw as DrawService;

class Draw
{
    /**
     * @param int $width
     * @param int $height
     *
     * @throws SetError
     *
     * @return DrawService
     */
    public static function create(int $width, int $height): DrawService
    {
        $image = Image::create();
        $image->create($width, $height);
        $image->fillTransparent();

        return self::createByImage($image);
    }

    /**
     * @param string $filename
     *
     * @throws FileNotFound
     * @throws SetError
     *
     * @return DrawService
     */
    public static function createByFilename(string $filename): DrawService
    {
        $image = Image::create();
        $image->load($filename);

        return self::createByImage($image);
    }

    /**
     * @param ImageService $image
     *
     * @return DrawService
     */
    public static function createByImage(ImageService $image): DrawService
    {
        return new DrawService($image);
    }
}
