<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\Image;

use GibsonOS\Core\Factory\FileFactory;
use GibsonOS\Core\Service\Image\Draw;

class DrawFactory
{
    /**
     * @return Draw
     */
    public static function create(): Draw
    {
        return new Draw(FileFactory::create());
    }
}
