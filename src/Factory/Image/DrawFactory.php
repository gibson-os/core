<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\Image;

use GibsonOS\Core\Factory\FileFactory;
use GibsonOS\Core\Service\Image\DrawService;

class DrawFactory
{
    /**
     * @return DrawService
     */
    public static function create(): DrawService
    {
        return new DrawService(FileFactory::create());
    }
}
