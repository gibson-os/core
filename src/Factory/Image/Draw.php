<?php
namespace GibsonOS\Core\Factory\Image;

use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Service\Image;
use GibsonOS\Core\Service\Image\Draw as DrawService;

class Draw
{
    /**
     * @param int $width
     * @param int $height
     * @param bool $trueColor
     * @param bool $fillTransparent
     * @return DrawService
     */
    static function create($width, $height, $trueColor = true, $fillTransparent = true)
    {
        $image = new Image();
        $image->create($width, $height, $trueColor, $fillTransparent);

        return self::createByImage($image);
    }

    /**
     * @param string $filename
     * @return DrawService
     * @throws FileNotFound
     */
    static function createByFilename($filename)
    {
        $image = new Image();
        $image->load($filename);

        return self::createByImage($image);
    }

    /**
     * @param Image $image
     * @return DrawService
     */
    static function createByImage(Image $image)
    {
        return new DrawService($image);
    }
}