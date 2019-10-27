<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\File;

use GibsonOS\Core\Service\File\Type as TypeService;

class Type
{
    public static function create(): TypeService
    {
        return new TypeService();
    }
}
