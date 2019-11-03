<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\DirService;

class DirFactory
{
    public static function create(): DirService
    {
        return new DirService();
    }
}
