<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\File as FileService;

class File
{
    public static function create(): FileService
    {
        return new FileService(Dir::create());
    }
}
