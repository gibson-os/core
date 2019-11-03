<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\File;

class FileFactory
{
    /**
     * @return File
     */
    public static function create(): File
    {
        return new File(DirFactory::create());
    }
}
