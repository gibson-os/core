<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\Dir;

class DirFactory
{
    public static function create(): Dir
    {
        return new Dir();
    }
}
