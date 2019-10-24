<?php
namespace GibsonOS\Core\Factory\Image;

use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Service\Image;
use GibsonOS\Core\Service\Image\Manipulate as ManipulateService;

class Manipulate
{
    /**
     * @param int $width
     * @param int $height
     * @param bool $trueColor
     * @param bool $fillTransparent
     * @return ManipulateService
     */
    static function create($width, $height, $trueColor = true, $fillTransparent = true)
    {
        $image = new Image();
        $image->create($width, $height, $trueColor, $fillTransparent);

        return self::createByImage($image);
    }

    /**
     * @param string $filename
     * @return ManipulateService
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
     * @return ManipulateService
     */
    static function createByImage(Image $image)
    {
        return new ManipulateService($image);
    }
}