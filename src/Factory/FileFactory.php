<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\FileService;

class FileFactory
{
    /**
     * @return FileService
     */
    public static function create(): FileService
    {
        return new FileService(DirFactory::create());
    }
}
