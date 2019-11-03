<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\Image;

use GibsonOS\Core\Factory\File;
use GibsonOS\Core\Service\Image\Draw as DrawService;

class Draw
{
    /**
     * @return DrawService
     */
    public static function create(): DrawService
    {
        return new DrawService(File::create());
    }
}
