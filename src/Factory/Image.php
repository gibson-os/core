<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\Image as ImageService;

class Image
{
    public static function create(): ImageService
    {
        return new ImageService(File::create());
    }
}
