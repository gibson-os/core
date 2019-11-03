<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\Image;

class ImageFactory
{
    /**
     * @return Image
     */
    public static function create(): Image
    {
        return new Image(FileFactory::create());
    }
}
