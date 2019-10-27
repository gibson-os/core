<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\Image;

use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Factory\Image;
use GibsonOS\Core\Service\Image as ImageService;
use GibsonOS\Core\Service\Image\Manipulate as ManipulateService;

class Manipulate
{
    /**
     * @param int $width
     * @param int $height
     *
     * @throws SetError
     *
     * @return ManipulateService
     */
    public static function create(int $width, int $height): ManipulateService
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
     * @return ManipulateService
     */
    public static function createByFilename(string $filename): ManipulateService
    {
        $image = Image::create();
        $image->load($filename);

        return self::createByImage($image);
    }

    /**
     * @param ImageService $image
     *
     * @return ManipulateService
     */
    public static function createByImage(ImageService $image): ManipulateService
    {
        return new ManipulateService($image);
    }
}
