<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\Dir as DirService;

class Dir
{
    public static function create(): DirService
    {
        return new DirService();
    }
}
