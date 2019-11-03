<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\File;

use GibsonOS\Core\Service\File\TypeService;

class TypeFactory
{
    /**
     * @return TypeService
     */
    public static function create(): TypeService
    {
        return new TypeService();
    }
}
